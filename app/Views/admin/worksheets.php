<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Worksheets</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div id="printCustomizationModal" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<span class="modal-title"><i class="fa fa-print"></i> Print Options</span>
			</div>
			<div class="modal-body">
				<div class="d-flex justify-content-between mb-2">
					<label for="worksheetTitle">Worksheet Title</label>
					<div style="width: 210px;">
						<input type="text" class="form-control" id="worksheetTitle" placeholder="Worksheet Title">
					</div>
				</div>

				<div class="d-flex justify-content-between mb-2">
					<label>What is Your Paper Size?</label>
					<div style="width: 210px;">
						<select class="form-control" id="paperSize">
							<option value="letter">Letter (8.5 x 11 inches)</option>
							<option value="tabloid">Tabloid (11 x 17 inches)</option>
							<option value="a0">A0 (33.1 x 46.8 inches)</option>
							<option value="a1">A1 (23.4 x 33.1 inches)</option>
							<option value="a2">A2 (16.5 x 23.4 inches)</option>
							<option value="a3">A3 (11.7 x 16.5 inches)</option>
							<option value="a4">A4 (8.27 x 11.69 inches)</option>
							<option value="a5">A5 (5.82 x 8.26 inches)</option>
						</select>
					</div>
				</div>

				<div class="d-flex justify-content-between">
					<label>No. of Columns</label>
					<div style="width: 210px;">
						<select class="form-control" id="columnsSelect">
							<option>1</option>
							<option>2</option>
							<option>3</option>
							<option>4</option>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a target="_blank" href="<?= base_url('admin/worksheets/print') ?>" id="printLink" class="btn btn-sm btn-outline-primary">Print</a>
				<button type="button" class="grey-bg pt-1 pb-1 pl-2 pr-2 btn btn-sm btn-outline-danger" data-dismiss="modal">Cancel</button>
			</div>
		</div>

	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Form to select grade, topic and level -->
                <form>
                    <div class="form-group">
                        <label for="grade">Grade</label>
                        <select class="form-control" id="grade" name="grade">
                            <option value="" selected>All Grades</option>
                            <?php
                            foreach ($grades as $grade) {
                                ?>
                                <option value="<?= $grade['id'] ?>" <?= (isset($filterGradeId) && $filterGradeId == $grade['id']) ? 'selected' : '' ?>><?= $grade['name'] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="topic">Topic <?= isset($filterGradeId) && !empty($filterGradeId) ? '<small class="text-muted">(showing topics for selected grade)</small>' : '' ?></label>
                        <select class="form-control" id="topic" name="topic">
                            <option value="" selected disabled>Select Topic</option>
                            <?php
                            foreach ($topics as $topic) {
                                ?>
                                <option value="<?= $topic['id'] ?>"><?= $topic['name'] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="level">Level</label>
                        <select class="form-control" id="level" name="level">
                            <option value="" selected disabled>Select Level</option>
                            <option value="1">Level 1</option>
                            <option value="2">Level 2</option>
                            <option value="3">Level 3</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="includeAnswers" name="includeAnswers">
                            <label class="form-check-label" for="includeAnswers">Include Answers</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="button" id="generateWorksheetBtn" class="btn btn-primary">Generate Worksheet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(document).ready(function() {
        $("#grade").change(function() {
            let gradeId = $(this).val();
            window.location.href = baseUrl + 'admin/worksheets?gradeId=' + gradeId;
        });

        $("#generateWorksheetBtn").click(function() {

            let topicId = $("#topic").val();
            let level = $("#level").val();

            if (!topicId || !level) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a topic and level',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            $("#printCustomizationModal").modal("show");
        });

        $("#columnsSelect, #paperSize, #worksheetTitle, #grade, #topic, #level, #includeAnswers").on("change keyup", function(e) {

            let gradeId = $("#grade").val();
            let topicId = $("#topic").val();
            let level = $("#level").val();
            let answers = $("#includeAnswers").is(":checked") ? 1 : 0;

            if (!topicId || !level) {
                return;
            }

            let columns = $("#columnsSelect").val();
            let paperSize = $("#paperSize").val();
            let worksheetTitle = $("#worksheetTitle").val();

            const params = new URLSearchParams({
                cols: columns,
                paperSize: paperSize,
                title: worksheetTitle,
                gradeId: gradeId,
                topicId: topicId,
                level: level,
                answers: answers
            });

            let url = $("#printLink").attr("href").split("?")[0] + "?" + decodeURIComponent(params.toString());
            $("#printLink").attr("href", url);
        });
    });
</script>
<?= $this->endSection() ?>