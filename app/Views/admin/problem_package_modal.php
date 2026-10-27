<div class="modal fade" id="packageImportModal" tabindex="-1" aria-labelledby="packageImportTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="packageImportTitle">Import Problem Package</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <form action="<?= site_url('admin/questions/import-package') ?>" method="post" enctype="multipart/form-data" data-upload-form id="packageImportForm">
            <div class="modal-body">
                <p class="text-muted">Choose the topic, difficulty and grade for every question in this package.</p>
                <div class="row">
                    <div class="col-md-6 form-group"><label for="packageTopic">Topic</label><select class="form-control" id="packageTopic" name="topicId" required>
                        <option value="">Select topic</option>
                        <?php foreach ($topics as $topic): if ($topic['owner_id'] != $user['id']) continue; ?>
                            <option value="<?= (int) $topic['id'] ?>"><?= esc($topic['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                    <div class="col-md-6 form-group"><label for="packageLevel">Difficulty Level</label><select class="form-control" id="packageLevel" name="difficulty" required>
                        <option value="1">Level 1 - Easy</option><option value="2">Level 2 - Medium</option><option value="3">Level 3 - Hard</option>
                    </select></div>
                    <div class="col-md-6 form-group"><label for="packageGrade">Grade</label><select class="form-control" id="packageGrade" name="gradeId" required>
                        <option value="">Select grade</option>
                        <?php foreach ($grades as $grade): ?><option value="<?= (int) $grade['id'] ?>"><?= esc($grade['name']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="col-md-6 form-group"><label for="problemPackage">ZIP Package</label><input class="form-control-file" id="problemPackage" name="package" type="file" accept=".zip" required aria-describedby="packageHelp"></div>
                </div>
                <div class="alert alert-light border" id="packageHelp">
                    <p class="mb-2">ZIP root: <code>problems.csv</code> and an <code>images/</code> folder containing any referenced images. No enclosing package folder.</p>
                    <p class="mb-2 small"><strong>Exact CSV headers:</strong> <code>question_html,question_type,answer,solution_html,image_files</code></p>
                    <ul class="pl-3 mb-2 small">
                        <li>Text alternatives: <code>6||six</code>. MCQ choices: <code>4|*6|8|10</code> (one correct choice).</li>
                        <li>Images: <code>diagram.png|chart.jpg</code>, or leave empty. Use <code>&lt;img src="images/diagram.png" alt="Diagram"&gt;</code> to place an image; otherwise it appears below the question.</li>
                        <li>Optional tags in question_html: <code>&lt;!-- tags: fractions|word-problems --&gt;</code>.</li>
                        <li>Up to 1,000 questions and 200 images. ZIP ≤25 MB; uncompressed contents ≤50 MB; each image ≤5 MB. PNG, JPG, JPEG, WebP and GIF only.</li>
                    </ul>
                    <p class="mb-0 small">Every row needs a solution explanation. The complete package is validated before saving. If anything fails, nothing is imported.</p>
                </div>
                <div class="d-flex flex-wrap mb-3" style="gap:8px;">
                    <a href="<?= site_url('admin/questions/sample-csv') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i> Download Sample CSV</a>
                    <button class="btn btn-sm btn-outline-primary" type="button" id="copyPackagePrompt"><i class="fa fa-copy"></i> Copy ChatGPT Prompt</button>
                    <a href="<?= site_url('admin/images') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Images Library <i class="fa fa-external-link-alt"></i></a>
                </div>
                <details id="packagePromptDetails"><summary class="text-muted small mb-2">View or manually copy the ChatGPT prompt</summary>
                    <label class="sr-only" for="packageChatGptPrompt">ChatGPT package prompt</label>
                    <textarea id="packageChatGptPrompt" class="form-control small" rows="12" readonly><?= esc(file_get_contents(APPPATH . 'Resources/problem-package-prompt.txt')) ?></textarea>
                </details>
                <div class="mt-3 d-none" data-upload-result role="status" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                <a href="<?= site_url('admin/questions') ?>" class="btn btn-sm btn-outline-primary d-none" data-upload-refresh>View imported questions</a>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-file-archive"></i> Import Package</button>
            </div>
        </form>
    </div></div>
</div>
