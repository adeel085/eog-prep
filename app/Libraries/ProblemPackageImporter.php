<?php

namespace App\Libraries;

use App\Models\{QuestionModel, QuestionAnswersModel, TopicQuestionsModel, TopicModel, GradeModel};
use CodeIgniter\Database\BaseConnection;

class ProblemPackageImporter
{
    public const HEADERS = ['question_html', 'question_type', 'answer', 'solution_html', 'image_files'];
    public const MAX_ZIP_BYTES = 25 * 1024 * 1024;
    private BaseConnection $db;
    private ImageLibrary $library;

    public function __construct(?BaseConnection $db = null, ?ImageLibrary $library = null)
    {
        $this->db = $db ?? db_connect();
        $this->library = $library ?? new ImageLibrary($this->db);
    }

    public function import(string $path, int $ownerId, string $topicId, string $level, string $gradeId): array
    {
        if (!ctype_digit($topicId) || !(new TopicModel($this->db))->where(['id' => $topicId, 'owner_id' => $ownerId])->first()) {
            throw new PackageValidationException(['Select a topic that belongs to your account.']);
        }
        if (!in_array($level, ['1', '2', '3'], true)) {
            throw new PackageValidationException(['Select difficulty level 1, 2 or 3.']);
        }
        if (!ctype_digit($gradeId) || !(new GradeModel($this->db))->find($gradeId)) {
            throw new PackageValidationException(['Select an existing grade.']);
        }
        ImageLibrary::requireTransactionalTables($this->db, ['questions', 'questions_answers', 'topics_questions', 'images_library']);
        if (!$this->db->fieldExists('tags', 'questions')) {
            throw new PackageValidationException(['Run the Problem Package Importer migration first (questions.tags is missing).']);
        }
        $package = self::validatePackage($path);
        $activeRow = null;
        if (!$this->db->transBegin()) {
            throw new \RuntimeException('Could not start import transaction.');
        }
        try {
            $urls = [];
            foreach ($package['images'] as $name => $image) {
                $urls[$name] = $this->library->persist($image, $ownerId)['url'];
            }
            $questions = new QuestionModel($this->db);
            $answers = new QuestionAnswersModel($this->db);
            $topics = new TopicQuestionsModel($this->db);
            foreach ($package['rows'] as $row) {
                $activeRow = $row['number'];
                $map = [];
                foreach ($row['image_files'] as $name) {
                    $map[$name] = $urls[$name];
                    $map['images/' . $name] = $urls[$name];
                }
                $questionHtml = PackageHtml::clean($row['question_html'], $map);
                $solutionHtml = PackageHtml::clean($row['solution_html'], $map);
                // Listing image_files alone is enough: append images not explicitly placed in either HTML field.
                foreach ($row['image_files'] as $name) {
                    if (!str_contains($questionHtml . $solutionHtml, htmlspecialchars($urls[$name], ENT_QUOTES, 'UTF-8'))) {
                        $questionHtml .= '<p><img src="' . htmlspecialchars($urls[$name], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"></p>';
                    }
                }
                if (strlen($questionHtml) > 65000 || strlen($solutionHtml) > 65000) {
                    throw new \RuntimeException('Expanded HTML exceeds the question storage limit.');
                }
                $id = $questions->insert(['level' => $level, 'question_type' => $row['question_type'],
                    'question_html' => $questionHtml, 'solution_html' => $solutionHtml, 'tags' => implode('|', $row['tags']),
                    'owner_id' => $ownerId, 'grade_id' => $gradeId, 'calculator_enabled' => 0]);
                if (!$id || $topics->insert(['topic_id' => $topicId, 'question_id' => $id], false) === false) {
                    throw new \RuntimeException('Could not save question or topic.');
                }
                foreach ($row['answers'] as $answer) {
                    if ($answers->insert(['question_id' => $id, 'answer' => $answer['answer'], 'is_correct' => $answer['is_correct']], false) === false) {
                        throw new \RuntimeException('Could not save answer.');
                    }
                }
            }
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new \RuntimeException('Could not commit import.');
            }
            $this->library->commitFiles();
        } catch (\Throwable $error) {
            $this->db->transRollback();
            $this->library->rollbackFiles();
            log_message('error', 'Problem package import failed: {error}', ['error' => $error->getMessage()]);
            throw new PackageValidationException([($activeRow ? "Row {$activeRow}: " : '') . 'Import could not be saved. No questions or images were imported. Check storage and database configuration.']);
        }
        return ['questions' => count($package['rows']), 'images' => count($package['images'])];
    }

    public static function validatePackage(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new PackageValidationException(['The server needs the PHP ZIP extension to import packages.']);
        }
        if (!is_file($path) || filesize($path) === 0 || filesize($path) > self::MAX_ZIP_BYTES) {
            throw new PackageValidationException(['The ZIP package must be between 1 byte and 25 MB.']);
        }
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CHECKCONS) !== true) {
            throw new PackageValidationException(['Upload a valid, unencrypted ZIP package.']);
        }
        try {
            if ($zip->numFiles > 202) {
                throw new PackageValidationException(['A package may contain problems.csv and at most 200 images.']);
            }
            $entries = [];
            $names = [];
            $total = 0;
            $imageCount = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];
                $lower = strtolower($name);
                if (isset($names[$lower]) || !preg_match('~\A(?:problems\.csv|images/|images/[A-Za-z0-9][A-Za-z0-9._-]{0,119}\.(?:png|jpe?g|webp|gif))\z~i', $name)
                    || str_contains($name, '..') || str_contains($name, '\\') || str_contains($name, "\0")) {
                    throw new PackageValidationException(["Unsafe, duplicate or unexpected ZIP entry: {$name}. Use problems.csv at the root and flat images/ filenames."]);
                }
                if (($name !== 'problems.csv' && !str_starts_with($name, 'images/')) || ($stat['encryption_method'] ?? 0) !== 0) {
                    throw new PackageValidationException(["Invalid or encrypted ZIP entry: {$name}."]);
                }
                $opsys = $attributes = 0;
                $zip->getExternalAttributesIndex($i, $opsys, $attributes);
                $mode = ($attributes >> 16) & 0170000;
                if (($opsys === \ZipArchive::OPSYS_UNIX && !in_array($mode, [0, 0100000, 0040000], true))
                    || ($mode === 0040000 && $name !== 'images/')) {
                    throw new PackageValidationException(["ZIP links and special files are forbidden: {$name}."]);
                }
                $names[$lower] = true;
                $total += $stat['size'];
                if ($stat['size'] > 5 * 1024 * 1024 || $total > 50 * 1024 * 1024 || ($stat['comp_size'] > 0 && $stat['size'] / $stat['comp_size'] > 200)) {
                    throw new PackageValidationException(['ZIP contents exceed the 5 MB per-file, 50 MB total or compression-ratio limit.']);
                }
                if ($name !== 'images/') {
                    if ($name !== 'problems.csv' && ++$imageCount > 200) {
                        throw new PackageValidationException(['A package may contain at most 200 images.']);
                    }
                    $entries[$name] = $i;
                }
            }
            if (!isset($entries['problems.csv'])) {
                throw new PackageValidationException(['The ZIP must contain problems.csv at its root.']);
            }
            $csv = self::readEntry($zip, 'problems.csv');
            $records = self::parseCsv($csv);
            $header = array_shift($records);
            if ($header !== self::HEADERS) {
                throw new PackageValidationException(['CSV headers must be exactly: ' . implode(',', self::HEADERS)]);
            }
            if (count($records) === 0 || count($records) > 1000) {
                throw new PackageValidationException(['Include between 1 and 1,000 question rows.']);
            }
            $rows = $errors = $referenced = [];
            foreach ($records as $index => $record) {
                $number = $index + 2;
                try {
                    $row = self::validateRow($record, $number, $entries);
                    $rows[] = $row;
                    foreach ($row['image_files'] as $image) {
                        $referenced[$image] = true;
                    }
                } catch (\InvalidArgumentException $error) {
                    $errors[] = "Row {$number}: " . $error->getMessage();
                    if (count($errors) >= 30) {
                        $errors[] = 'Showing the first 30 errors. Fix these and retry the entire package.';
                        break;
                    }
                }
            }
            if ($errors) {
                throw new PackageValidationException($errors);
            }
            $images = [];
            foreach ($entries as $name => $index) {
                if ($name === 'problems.csv') {
                    continue;
                }
                $basename = substr($name, 7);
                try {
                    $image = ImageLibrary::validate(self::readEntry($zip, $name), $basename);
                } catch (PackageValidationException $error) {
                    $rowNumbers = array_column(array_filter($rows, static fn ($row) => in_array($basename, $row['image_files'], true)), 'number');
                    throw new PackageValidationException([($rowNumbers ? 'Row(s) ' . implode(', ', $rowNumbers) . ': ' : '') . $error->getMessage()]);
                }
                if (isset($referenced[$basename])) {
                    $images[$basename] = $image;
                }
            }
            return ['rows' => $rows, 'images' => $images];
        } finally {
            $zip->close();
        }
    }

    private static function readEntry(\ZipArchive $zip, string $name): string
    {
        $stream = @$zip->getStream($name);
        if (!$stream) {
            throw new PackageValidationException(["Could not read ZIP entry: {$name}."]);
        }
        try {
            $bytes = @stream_get_contents($stream, 5 * 1024 * 1024 + 1);
            $stat = $zip->statName($name);
            if ($bytes === false || strlen($bytes) !== $stat['size'] || strlen($bytes) > 5 * 1024 * 1024 || hash('crc32b', $bytes) !== sprintf('%08x', $stat['crc'])) {
                throw new PackageValidationException(["Invalid or oversized ZIP entry: {$name}."]);
            }
            return $bytes;
        } finally {
            fclose($stream);
        }
    }

    // Strict RFC 4180 parser: handles quoted HTML, commas and multiline fields, and rejects unclosed/misplaced quotes.
    public static function parseCsv(string $csv): array
    {
        $csv = preg_replace('/\A\xEF\xBB\xBF/', '', $csv);
        if (!mb_check_encoding($csv, 'UTF-8') || str_contains($csv, "\0")) {
            throw new PackageValidationException(['CSV must be UTF-8 without NUL bytes.']);
        }
        $rows = $row = [];
        $field = '';
        $state = 'start';
        $length = strlen($csv);
        for ($i = 0; $i < $length; $i++) {
            $c = $csv[$i];
            if ($state === 'quoted') {
                if ($c === '"') {
                    if ($i + 1 < $length && $csv[$i + 1] === '"') {
                        $field .= '"';
                        $i++;
                    } else {
                        $state = 'closed';
                    }
                } else {
                    $field .= $c;
                }
                continue;
            }
            if ($c === ',' || $c === "\n" || $c === "\r") {
                $row[] = $field;
                $field = '';
                $state = 'start';
                if ($c !== ',') {
                    $rows[] = $row;
                    $row = [];
                    if (count($rows) > 1001) {
                        throw new PackageValidationException(['A package may contain at most 1,000 question rows.']);
                    }
                    if ($c === "\r" && $i + 1 < $length && $csv[$i + 1] === "\n") {
                        $i++;
                    }
                }
            } elseif ($c === '"' && $state === 'start') {
                $state = 'quoted';
            } elseif ($c === '"' || $state === 'closed') {
                throw new PackageValidationException(['Row ' . (count($rows) + 1) . ': malformed CSV quoting. Double quotes inside quoted fields must be doubled.']);
            } else {
                $field .= $c;
                $state = 'unquoted';
            }
        }
        if ($state === 'quoted') {
            throw new PackageValidationException(['Row ' . (count($rows) + 1) . ': unclosed quoted CSV field.']);
        }
        if ($row || $field !== '' || $state !== 'start') {
            $row[] = $field;
            $rows[] = $row;
        }
        return $rows;
    }

    private static function validateRow(array $record, int $number, array $entries): array
    {
        if (count($record) !== 5) {
            throw new \InvalidArgumentException('Expected exactly 5 columns; quote HTML containing commas or quotes.');
        }
        $row = array_combine(self::HEADERS, $record);
        $type = trim($row['question_type']);
        if (!in_array($type, ['text', 'mcq'], true)) {
            throw new \InvalidArgumentException('question_type must be text or mcq.');
        }
        $filenames = trim($row['image_files']) === '' ? [] : array_map('trim', explode('|', $row['image_files']));
        if (count($filenames) > 20 || count($filenames) !== count(array_unique($filenames))) {
            throw new \InvalidArgumentException('List at most 20 unique image filenames.');
        }
        $map = [];
        foreach ($filenames as $filename) {
            if (!isset($entries['images/' . $filename])) {
                throw new \InvalidArgumentException("Missing image: images/{$filename}. Filenames are case-sensitive.");
            }
            $map[$filename] = $map['images/' . $filename] = 'https://package.invalid/' . $filename;
        }
        $tags = [];
        preg_match_all('/<!--\s*tags:\s*(.*?)\s*-->/si', $row['question_html'], $matches);
        if (count($matches[1]) > 1) {
            throw new \InvalidArgumentException('Use only one tags comment.');
        }
        if ($matches[1]) {
            $tags = array_map('trim', explode('|', $matches[1][0]));
            if (count($tags) > 20 || strlen(implode('|', $tags)) > 1000) {
                throw new \InvalidArgumentException('Use at most 20 tags totaling 1,000 bytes.');
            }
            foreach ($tags as $tag) {
                if ($tag === '' || mb_strlen($tag) > 60 || preg_match('/[<>\x00-\x1f]/u', $tag)) {
                    throw new \InvalidArgumentException('Tags must be nonempty plain text of at most 60 characters.');
                }
            }
            $tags = array_values(array_unique($tags));
        }
        try {
            PackageHtml::clean($row['question_html'], $map);
            PackageHtml::clean($row['solution_html'], $map);
        } catch (\InvalidArgumentException $error) {
            throw new \InvalidArgumentException('question_html / solution_html: ' . $error->getMessage());
        }
        $choices = explode($type === 'text' ? '||' : '|', trim($row['answer']));
        if (count($choices) > 20 || ($type === 'mcq' && count($choices) < 2)) {
            throw new \InvalidArgumentException('Use 1–20 text alternatives or 2–20 MCQ choices.');
        }
        $answers = [];
        $correctCount = 0;
        $seen = [];
        foreach ($choices as $choice) {
            $choice = trim($choice);
            $correct = $type === 'text' || str_starts_with($choice, '*');
            if ($type === 'mcq' && $correct) {
                $choice = ltrim(substr($choice, 1));
            }
            if ($choice === '' || strlen($choice) > 1000 || ($type === 'text' && str_contains($choice, '|'))) {
                throw new \InvalidArgumentException('Answers must be nonempty and at most 1,000 bytes; text alternatives use ||.');
            }
            if ($type === 'mcq') {
                // MCQ choices are HTML in the existing editor; text alternatives remain plain text.
                $choice = PackageHtml::clean($choice, []);
            } elseif ($choice !== strip_tags($choice)) {
                throw new \InvalidArgumentException('Text answers must be plain text.');
            }
            $key = trim(html_entity_decode(strip_tags($choice), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (isset($seen[$key])) {
                throw new \InvalidArgumentException('Duplicate answer choices are not allowed.');
            }
            $seen[$key] = true;
            $correctCount += $correct ? 1 : 0;
            $answers[] = ['answer' => $choice, 'is_correct' => $correct ? 1 : 0];
        }
        if ($type === 'mcq' && $correctCount !== 1) {
            throw new \InvalidArgumentException('Prefix exactly one MCQ choice with *. Separate choices with a single |.');
        }
        return ['number' => $number, 'question_type' => $type, 'question_html' => $row['question_html'],
            'solution_html' => $row['solution_html'], 'answers' => $answers, 'image_files' => $filenames, 'tags' => $tags];
    }
}
