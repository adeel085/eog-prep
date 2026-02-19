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
				<a target="_blank" href="#" id="printLink" class="btn btn-sm btn-outline-primary">Print</a>
				<button type="button" class="grey-bg pt-1 pb-1 pl-2 pr-2 btn btn-sm btn-outline-danger" data-dismiss="modal">Cancel</button>
			</div>
		</div>

	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-sm btn-primary" id="addRowBtn">
                        <i class="fa fa-plus"></i> Add Row
                    </button>
                </div>
                <!-- Form to select grade, topic and level -->
                <form class="mt-4">
                    <table class="table" id="rowsTable">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Topic</th>
                                <th>Level 1</th>
                                <th>Level 2</th>
                                <th>Level 3</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="no-rows-added">
                                <td colspan="6" class="text-center">No rows added yet</td>
                            </tr>
                        </tbody>
                    </table>

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

        let topics = <?= json_encode($topics) ?>;
        let grades = <?= json_encode($grades) ?>;

        $("#addRowBtn").click(function() {
            $("#rowsTable tbody").append(`
                <tr>
                    <td>
                        <select class="form-control grade-select">
                            <option value="" selected>All Grades</option>
                            ${grades.map(grade => `<option value="${grade.id}">${grade.name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select class="form-control topic-select">
                            <option value="" disabled selected>-- Select Topic --</option>
                            ${topics.map(topic => `<option value="${topic.id}">${topic.name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control max-questions-l1" placeholder="Max Questions">
                    </td>
                    <td>
                        <input type="text" class="form-control max-questions-l2" placeholder="Max Questions">
                    </td>
                    <td>
                        <input type="text" class="form-control max-questions-l3" placeholder="Max Questions">
                    </td>
                    <td>
                        <div class="text-right">
                            <button type="button" class="btn btn-sm btn-danger remove-row-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);

            $(".no-rows-added").css("display", "none");
        });

        $(document).on("change", ".grade-select", function() {
            let gradeId = $(this).val();
            let grade = grades.find(grade => grade.id == gradeId);
            
            if (grade) {
                $(this).closest("tr").find(".topic-select").html(`
                    <option value="" disabled selected>-- Select Topic --</option>
                    ${grade.topics.map(topic => `<option value="${topic.id}">${topic.name}</option>`).join('')}
                `);

                if (grade.topics.length == 0) {
                    new Notify({
                        title: 'Warning',
                        text: 'No topics found for the selected grade. Please select All Grades to see all topics.',
                        status: 'warning',
                        autoclose: true,
                        autotimeout: 3000
                    });
                    return;
                }
            }
            else {
                $(this).closest("tr").find(".topic-select").html(`
                    <option value="" disabled selected>-- Select Topic --</option>
                    ${topics.map(topic => `<option value="${topic.id}">${topic.name}</option>`).join('')}
                `);
            }

            $(this).closest("tr").find(".topic-select").val("").trigger("change");
        });

        $(document).on("click", ".remove-row-btn", function() {
            $(this).closest("tr").remove();

            if ($("#rowsTable tbody tr").length == 1) {
                $(".no-rows-added").css("display", "table-row");
            }
        });

        $("#generateWorksheetBtn").click(async function() {

            let criteria = [];

            let error = null;

            $("#rowsTable tbody tr:not(.no-rows-added)").each(function() {
                let gradeId = $(this).find(".grade-select").val();
                let topicId = $(this).find(".topic-select").val();
                let level1QuestionsCount = $(this).find(".max-questions-l1").val();
                let level2QuestionsCount = $(this).find(".max-questions-l2").val();
                let level3QuestionsCount = $(this).find(".max-questions-l3").val();
                
                if (!topicId || !level1QuestionsCount || !level2QuestionsCount || !level3QuestionsCount) {
                    error = "Please select a grade, topic and enter max questions for all levels";
                    return;
                }

                criteria.push({
                    gradeId: gradeId,
                    topicId: topicId,
                    level1QuestionsCount: level1QuestionsCount,
                    level2QuestionsCount: level2QuestionsCount,
                    level3QuestionsCount: level3QuestionsCount
                });
            });

            if (error) {
                new Notify({
                    title: 'Error',
                    text: error,
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {
                let formData = new FormData();
                formData.append('criteria', JSON.stringify(criteria));

                let res = await ajaxCall({
                    url: baseUrl + 'admin/worksheets/get-questions',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {

                    window.questionsIds = res.data.questionsIds;
                    updatePrintLink();

                    $("#printCustomizationModal").modal("show");
                }
                else {
                    new Notify({
                        title: 'Error',
                        text: res.message,
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });
                }
            }
            catch (error) {
                console.log(error);
                if (error.status == 401) {
                    new Notify({
                        title: 'Error',
                        text: "Your session has expired. Redirecting to login page...",
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });
                    return;
                }

                new Notify({
                    title: 'Error',
                    text: error.responseJSON.message || 'Something went wrong',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }
        });

        $("#worksheetTitle, #paperSize, #columnsSelect, #includeAnswers").change(function() {
            updatePrintLink();
        });

        function updatePrintLink() {
            let worksheetTitle = $("#worksheetTitle").val();
            let paperSize = $("#paperSize").val();
            let columns = $("#columnsSelect").val();
            let includeAnswers = $("#includeAnswers").is(":checked") ? 1 : 0;

            let urlSearchParams = new URLSearchParams();
            urlSearchParams.append('worksheetTitle', worksheetTitle);
            urlSearchParams.append('paperSize', paperSize);
            urlSearchParams.append('columns', columns);
            urlSearchParams.append('includeAnswers', includeAnswers);
            urlSearchParams.append('questionsIds', window.questionsIds.join(','));

            $("#printLink").attr("href", baseUrl + 'admin/worksheets/print?' + urlSearchParams.toString());
        }
    });

    window.questionsIds = [];
</script>
<?= $this->endSection() ?>