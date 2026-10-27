<?php

// Dependency-free integration suite. Uses only a temporary SQLite database and temporary image files.
// Run from the repository root: php tests/package/run.php
chdir(dirname(__DIR__, 2));
define('FCPATH', getcwd() . DIRECTORY_SEPARATOR);
require 'system/Test/bootstrap.php';

use App\Libraries\{ImageLibrary, PackageHtml, PackageValidationException, ProblemPackageImporter};

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
function rejects(callable $call, string $message): void
{
    try {
        $call();
    } catch (PackageValidationException | InvalidArgumentException $error) {
        check(str_contains($error->getMessage(), $message), 'Wrong validation error: ' . $error->getMessage());
        return;
    }
    throw new RuntimeException('Expected rejection: ' . $message);
}
$temp = sys_get_temp_dir() . '/eog-package-test-' . bin2hex(random_bytes(6));
mkdir($temp, 0700);
function package(array $rows, array $files = [], ?array $headers = null): string
{
    global $temp;
    $stream = fopen('php://temp', 'w+');
    fputcsv($stream, $headers ?? ProblemPackageImporter::HEADERS, ',', '"', '');
    foreach ($rows as $row) fputcsv($stream, $row, ',', '"', '');
    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);
    return rawPackage(['problems.csv' => $csv] + $files);
}
function rawPackage(array $entries): string
{
    global $temp;
    $path = $temp . '/' . bin2hex(random_bytes(6)) . '.zip';
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE);
    foreach ($entries as $name => $bytes) $zip->addFromString($name, $bytes);
    $zip->close();
    return $path;
}
function removeTree(string $path): void
{
    if (is_dir($path)) {
        foreach (array_diff(scandir($path), ['.', '..']) as $entry) removeTree($path . '/' . $entry);
        rmdir($path);
    } else {
        unlink($path);
    }
}

try {
    $db = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => $temp . '/test.sqlite', 'DBPrefix' => '', 'DBDebug' => true], false);
    // Match the existing schema: neither answers nor topic associations have auto-increment IDs.
    $db->query('CREATE TABLE questions (id INTEGER PRIMARY KEY AUTOINCREMENT, level INTEGER, question_type TEXT, question_html TEXT, solution_html TEXT, owner_id INTEGER, grade_id INTEGER, calculator_enabled INTEGER, created_at TEXT, updated_at TEXT)');
    $db->query('CREATE TABLE questions_answers (question_id INTEGER, answer TEXT, is_correct INTEGER, created_at TEXT, updated_at TEXT)');
    $db->query('CREATE TABLE topics_questions (topic_id INTEGER, question_id INTEGER, created_at TEXT, updated_at TEXT)');
    $db->query('CREATE TABLE topics (id INTEGER PRIMARY KEY, owner_id INTEGER, name TEXT)');
    $db->query('CREATE TABLE grades (id INTEGER PRIMARY KEY, name TEXT)');
    $db->query("INSERT INTO topics VALUES (1, 7, 'Fractions'), (2, 8, 'Other teacher')");
    $db->query("INSERT INTO grades VALUES (3, 'Grade 3')");
    require APPPATH . 'Database/Migrations/2026-10-27-000001_AddImageLibraryAndQuestionTags.php';
    (new App\Database\Migrations\AddImageLibraryAndQuestionTags(\Config\Database::forge($db)))->up();
    $db->resetDataCache();
    check($db->tableExists('images_library') && $db->fieldExists('tags', 'questions'), 'Migration did not create the schema');
    $library = new ImageLibrary($db, $temp . '/images');
    $importer = new ProblemPackageImporter($db, $library);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII=');
    $text = ['<p>What is 3 + 3?</p><!-- tags: addition|number-sense -->', 'text', '6||six', '<p>Three plus three is six.</p>', ''];
    $mcq = ['<p>Choose six.</p><p><img src="images/a.png" alt="Diagram"></p>', 'mcq', '<p>4</p>|*<p>6</p>|<p>8</p>', '<p>Count six dots, then add.</p>', 'a.png'];
    $withSolutionImage = ['<p>Read this chart.</p>', 'text', '6', '<p><img src="a.png" alt="Chart"></p><p>Count the entries.</p>', 'a.png'];
    $path = package([$text, $mcq, $withSolutionImage], ['images/a.png' => $png]);
    $result = $importer->import($path, 7, '1', '2', '3');
    check($result === ['questions' => 3, 'images' => 1], 'Incorrect import counts');
    $questions = $db->table('questions')->orderBy('id')->get()->getResultArray();
    check(count($questions) === 3 && count(glob($temp . '/images/*')) === 1, 'Missing questions or image file');
    check($questions[0]['tags'] === 'addition|number-sense' && !str_contains($questions[0]['question_html'], '<!--'), 'Tag metadata did not persist correctly');
    check((int) $questions[1]['level'] === 2 && (int) $questions[1]['grade_id'] === 3 && (int) $questions[1]['owner_id'] === 7, 'Selected settings not applied');
    $image = $db->table('images_library')->get()->getRowArray();
    check(str_contains($questions[1]['question_html'], '/images/' . $image['public_id']), 'Image URL not replaced');
    check(str_contains($questions[2]['solution_html'], '/images/' . $image['public_id']), 'Solution image URL not replaced');
    check(substr_count($questions[2]['question_html'], '<img') === 0, 'Explicit solution image was appended twice');
    check($db->table('questions_answers')->where('question_id', 1)->where('is_correct', 1)->countAllResults() === 2, 'Alternative text answers not stored');
    check($db->table('questions_answers')->where('question_id', 2)->where('is_correct', 1)->countAllResults() === 1, 'Correct MCQ answer not stored');
    check($db->table('topics_questions')->where('topic_id', 1)->countAllResults() === 3, 'Topic associations missing');
    $before = $db->table('questions')->countAllResults();
    $bad = $text; $bad[2] = '';
    rejects(fn () => $importer->import(package([$text, $bad]), 7, '1', '2', '3'), 'Row 3');
    check($db->table('questions')->countAllResults() === $before, 'Malformed package partially imported');
    rejects(fn () => $importer->import($path, 7, '2', '2', '3'), 'belongs to your account');
    rejects(fn () => $importer->import($path, 7, '1', '4', '3'), 'difficulty');
    rejects(fn () => $importer->import($path, 7, '1', '2', '999'), 'existing grade');
    $bad = $mcq; $bad[2] = '*4|*6|8';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad], ['images/a.png' => $png])), 'exactly one');
    $bad[2] = '4|6|8';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad], ['images/a.png' => $png])), 'exactly one');
    $bad[2] = '*4||8';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad], ['images/a.png' => $png])), 'nonempty');
    $bad = $text; $bad[2] = '6|six';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad])), 'text alternatives use ||');
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$mcq])), 'Row 2: Missing image');
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$text], [], ['wrong', 'question_type', 'answer', 'solution_html', 'image_files'])), 'headers must be exactly');
    rejects(fn () => ProblemPackageImporter::validatePackage(rawPackage(['folder/problems.csv' => ''])), 'Unsafe');
    rejects(fn () => ProblemPackageImporter::validatePackage(rawPackage(['../problems.csv' => ''])), 'Unsafe');
    rejects(fn () => ProblemPackageImporter::validatePackage(rawPackage(['problems.csv' => '', 'images/a.svg' => '<svg/>'])), 'unexpected');
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$mcq], ['images/a.png' => 'not a PNG'])), 'Row(s) 2');
    rejects(fn () => ImageLibrary::validate($png, 'evil.svg'), 'use PNG');
    rejects(fn () => ImageLibrary::validate($png, 'wrong.jpg'), 'do not match');
    rejects(fn () => ImageLibrary::validate(str_repeat('x', 5 * 1024 * 1024 + 1), 'big.png'), '5 MB');
    rejects(fn () => ProblemPackageImporter::parseCsv("a,b\n\"unclosed"), 'Row 2: unclosed');
    rejects(fn () => ProblemPackageImporter::parseCsv('"a"b,c'), 'malformed CSV');
    rejects(fn () => ProblemPackageImporter::parseCsv('a"b,c'), 'malformed CSV');
    check(ProblemPackageImporter::parseCsv("\xEF\xBB\xBFa,b\r\n\"one, two\nthree\",\"a\"\"b\"\r\n")[1] === ["one, two\nthree", 'a"b'], 'Multiline / escaped CSV parsing failed');
    rejects(fn () => PackageHtml::clean('<p onclick="alert(1)">X</p>', []), 'Unsupported HTML attribute');
    rejects(fn () => PackageHtml::clean('<script>alert(1)</script><p>X</p>', []), 'Unsupported HTML element');
    rejects(fn () => PackageHtml::clean('<img src="https://evil.invalid/a.png">', []), 'Image src');
    rejects(fn () => PackageHtml::clean('<p> </p>', []), 'cannot be empty');
    check(str_contains(PackageHtml::clean('<p><span class="math-tex">\\(\\frac{1}{2}\\)</span></p>', []), '\\frac{1}{2}'), 'MathJax markup was damaged');
    // Symlink and duplicate filename defenses.
    $link = package([$text], ['images/link.png' => $png]);
    $zip = new ZipArchive(); $zip->open($link);
    $zip->setExternalAttributesName('images/link.png', ZipArchive::OPSYS_UNIX, 0120777 << 16); $zip->close();
    rejects(fn () => ProblemPackageImporter::validatePackage($link), 'links and special');
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$text], ['images/a.png' => $png, 'images/A.png' => $png])), 'duplicate');
    $bad = $text; $bad[1] = 'essay';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad])), 'Row 2: question_type');
    $bad = $text; $bad[3] = '';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad])), 'cannot be empty');
    $bad = $text; $bad[] = 'extra';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad])), 'exactly 5 columns');
    rejects(fn () => ProblemPackageImporter::validatePackage(package([])), 'between 1 and 1,000');
    $manyImages = [];
    for ($i = 0; $i < 201; $i++) $manyImages['images/pic-' . $i . '.png'] = $png;
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$text], $manyImages)), 'at most 200 images');
    $big = $text; $big[0] = '<p>' . base64_encode(random_bytes(46000)) . '</p>';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$big])), '60,000 bytes');
    $bad = $text; $bad[0] .= '<!-- tags: one||two -->';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad])), 'only one tags comment');
    $bad = $text; $bad[0] = '<p>X</p><!-- tags: one||two -->';
    rejects(fn () => ProblemPackageImporter::validatePackage(package([$bad])), 'nonempty plain text');
    $autoImage = $text; $autoImage[4] = 'a.png';
    $appendResult = $importer->import(package([$autoImage], ['images/a.png' => $png]), 7, '1', '1', '3');
    $appended = $db->table('questions')->orderBy('id', 'DESC')->get()->getRowArray();
    check(str_contains($appended['question_html'], '<img'), 'Listed image was not automatically appended');
    // Remove this additional successful case from baseline totals used below.
    $db->table('questions_answers')->where('question_id', $appended['id'])->delete();
    $db->table('topics_questions')->where('question_id', $appended['id'])->delete();
    $db->table('questions')->where('id', $appended['id'])->delete();
    // A real database failure after images and the first question were saved must roll back everything.
    $db->query("CREATE TRIGGER fail_answer BEFORE INSERT ON questions_answers WHEN NEW.answer = 'force-db-failure' BEGIN SELECT RAISE(ABORT, 'injected failure'); END");
    $failure = $text; $failure[2] = 'force-db-failure';
    $imageCount = $db->table('images_library')->countAllResults();
    rejects(fn () => $importer->import(package([$mcq, $failure], ['images/a.png' => $png]), 7, '1', '1', '3'), 'Row 3: Import could not be saved');
    check($db->table('questions')->countAllResults() === $before, 'Database failure left questions behind');
    check($db->table('images_library')->countAllResults() === $imageCount, 'Database failure left image metadata behind');
    check(count(glob($temp . '/images/*')) === $imageCount, 'Database failure left orphan image files');
    check($db->table('questions_answers')->countAllResults() === 6 && $db->table('topics_questions')->countAllResults() === 3, 'Database failure left answers / associations behind');
    $db->query('DROP TRIGGER fail_answer');
    // A corrected upload in a new request starts with a fresh connection/transaction state.
    $db = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => $temp . '/test.sqlite', 'DBPrefix' => '', 'DBDebug' => true], false);
    $library = new ImageLibrary($db, $temp . '/images');
    $importer = new ProblemPackageImporter($db, $library);
    $result = $importer->import(package([$text]), 7, '1', '3', '3');
    check($result['questions'] === 1, 'A corrected import could not be retried');
    // A storage failure after writing an image must also remove that file and its metadata.
    $failingLibrary = new class($db, $temp . '/images') extends ImageLibrary {
        public function persist(array $image, int $ownerId): array
        {
            parent::persist($image, $ownerId);
            throw new RuntimeException('injected storage failure');
        }
    };
    $beforeQuestions = $db->table('questions')->countAllResults();
    $beforeImages = $db->table('images_library')->countAllResults();
    rejects(fn () => (new ProblemPackageImporter($db, $failingLibrary))->import($path, 7, '1', '1', '3'), 'No questions or images');
    check($db->table('questions')->countAllResults() === $beforeQuestions && $db->table('images_library')->countAllResults() === $beforeImages, 'Storage failure left records behind');
    check(count(glob($temp . '/images/*')) === $beforeImages, 'Storage failure left files behind');
    // Downloadable sample is a valid package without any images.
    $sample = rawPackage(['problems.csv' => file_get_contents('public/downloads/problem-package-sample.csv')]);
    check(count(ProblemPackageImporter::validatePackage($sample)['rows']) === 2, 'Sample CSV is invalid');
    echo "PASS: {$checks} package, storage, migration and rollback checks.\n";
} catch (Throwable $error) {
    $suiteFailure = $error;
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
} finally {
    removeTree($temp);
}
if (isset($suiteFailure)) exit(1);
