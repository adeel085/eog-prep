# Images Library and Problem Package Importer

The Images Library is at `/admin/images`. Admins and teachers can upload raster images, see their own library, and copy permanent public image URLs. Question creation and editing also link to the library. URLs remain public for use in question HTML; library listings require authentication and are scoped to the uploader. Images are served with their validated MIME type and `nosniff` from private storage at `writable/images-library`, never executed as uploaded files. Back up this directory together with the database.

Question management retains its existing admin-only access. Use **Questions → Import Problem Package**, select an owned topic, level 1–3, and a grade, then upload the ZIP. All questions get those selections, the current admin as owner, and the calculator disabled. Question HTML and explanations use `questions`; accepted text alternatives and MCQ correctness use `questions_answers`; topic links use `topics_questions`. The existing editor can edit imported questions. Optional tags are stored in `questions.tags` and displayed in question management.

## Installation

Run `php spark migrate`, or apply `problem-package-importer.sql` once. Do not use both: the SQL adds the same column as the migration. The local development database has already been migrated. The three existing import tables must use InnoDB; the importer refuses to run against nontransactional tables. No existing question or answer data is changed by the migration.

PHP requires `zip`, `fileinfo`, `dom`, and `mbstring`, in addition to the existing application requirements. PHP must be able to create/write `writable/images-library`. Configure `upload_max_filesize = 25M`, `post_max_size = 30M` or higher, and an equivalent web-server request limit. Allow enough execution time and memory for up to 1,000 questions and 50 MB of decompressed content (256 MB PHP memory recommended).

## Package format

```text
problem-package.zip
├── problems.csv
└── images/
    ├── image-1.png
    └── image-2.jpg
```

Use exactly these UTF-8 CSV headers, in order:

```text
question_html,question_type,answer,solution_html,image_files
```

- Text answers use `||` for equivalent alternatives, e.g. `6||six`. Each is saved as a correct answer.
- MCQ choices use `|`, with exactly one `*` prefix, e.g. `4|*6|8|10`. MCQ choices can contain supported HTML and MathJax. No image choices or literal delimiter characters inside answers.
- Both question and solution HTML are required. Use `<p>` paragraphs. MathJax uses `<span class="math-tex">\( ... \)</span>` or `<div class="math-tex">\[ ... \]</div>`.
- Optional tags are encoded as one `<!-- tags: fractions|word-problems -->` comment in `question_html`. This keeps the five headers exact. The comment is removed from rendered content and its tags are saved separately. Up to 20 tags, 60 characters each, 1,000 bytes total.
- `image_files` can be empty. Otherwise list case-sensitive basenames separated by `|`, e.g. `triangle.png|chart.jpg`. Put actual files under `images/` with no subdirectories. Reference them as `<img src="images/triangle.png" alt="Triangle">` or use the basename as `src`. All HTML image sources must be listed in that row. Unplaced listed images are appended below the question. Shared image filenames are uploaded once per package. Unreferenced valid images are not added to the library.
- No enclosing ZIP folder, extra files, duplicate/case-colliding names, paths containing `..`, symlinks, special files, or encrypted entries. An image-free ZIP may contain only `problems.csv`.
- Up to 1,000 rows, 200 images, 25 MB ZIP, 50 MB total decompressed, 5 MB per file. Image types: PNG, JPG/JPEG, WebP, GIF; extensions and detected content must match. Maximum 10,000 pixels per side and 25 megapixels. Excessive compression ratios are rejected. Question/solution fields are limited to 60,000 bytes; answers to 1,000 bytes each, up to 20 alternatives/choices.
- CSV uses RFC 4180 quoting, supports multiline HTML and a UTF-8 BOM. Embedded quotes must be doubled. Blank or malformed rows are rejected with CSV record numbers (the header is row 1). The UI shows up to 30 row errors at once.
- Imported HTML uses an allowlist of CKEditor formatting, tables, raster images, and MathJax wrappers. Scripts, event handlers, styles, remote/data image URLs and embedded objects are rejected.

The modal offers an authenticated sample CSV download and a copyable ChatGPT prompt with the selected topic, level, and grade. The prompt covers formatting, worked solutions, tags, supplied/generated images, exact quoting, and independent shuffling of MCQ choices. The importer preserves the authored choice order and correct marker.

## Atomic imports

The complete ZIP, CSV, image references, image contents and row HTML are validated before writing anything. Images and question records are saved within one database transaction. Any handled persistence failure rolls back all new records and deletes files created by that attempt; existing library images remain intact. Library uploads follow the same transaction/cleanup pattern. No ZIP is extracted to disk. Upload temporary files are managed by PHP.

As with a database plus filesystem operation, a process or machine crash cannot be covered by the SQL transaction alone. Backups should include both stores; no crash recovery worker is installed by this feature.

## Verification

Run `php tests/package/run.php` (59 checks). It uses a disposable SQLite database and private temporary files, runs the actual migration, and checks package validation, stored alternatives and MCQ correctness, selected settings, image reuse and placement, tags, MathJax preservation, unsafe paths, missing/mistyped images, malformed CSV, invalid HTML, and rollback of files/records after injected database and storage failures.

Browser and HTTP verification also ran against a separate disposable MySQL schema matching the website's actual tables. It covered admin upload and URL copying, public image responses, missing-image feedback and successful retry, the package modal and prompt copying, sample download, imported editor content, teacher upload and owner-scoped listings, denied teacher imports, CSRF rejection, student upload denial, and rollback after an injected MySQL insert failure. The verification data is separate from the user's question bank.
