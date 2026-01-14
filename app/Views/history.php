<?= $this->extend('Layouts/default_2') ?>

<?= $this->section('head') ?>
<style>
    
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container" style="margin-top: 40px;">
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">My History</h4>

            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-3">View your weekly session report</p>
                    <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 12px;">
                        <label class="mb-0">
                            <input type="radio" name="weeklyReportType" value="last7days" checked> Last 7 days
                        </label>

                        <label class="mb-0">
                            <input type="radio" name="weeklyReportType" value="selectWeek"> Select Week
                        </label>

                        <div style="max-width: 200px; margin-left: 20px;">
                            <input class="form-control " type="week" id="weeklyReportWeek" style="display: none;">
                        </div>

                        <div>
                            <button class="btn btn-primary" id="viewReportBtn">View Report</button>
                        </div>
                    </div>

                    <div class="table-responsive mt-4" id="reportContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {

        $("input[name='weeklyReportType']").change(function() {
            if ($(this).val() == 'selectWeek') {
                $("#weeklyReportWeek").show();
            }
            else {
                $("#weeklyReportWeek").hide();
            }
        });

        $("#viewReportBtn").click(async () => {

            let weeklyReportType = $("input[name='weeklyReportType']:checked").val();

            let week = null;

            if (weeklyReportType == 'selectWeek') {
                week = $("#weeklyReportWeek").val();

                if (week == "") {
                    new Notify({
                        status: 'error',
                        title: 'Input Error',
                        text: 'Please select a week',
                        timeout: 3000,
                        autoclose: true
                    });
                    return;
                }
            }

            try {

                let formData = new FormData();
                if (weeklyReportType == 'selectWeek') {
                    formData.append('week', week);
                }

                // Show loader in button
                $('#viewReportBtn').attr('data-content', $('#viewReportBtn').html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');
                
                let res = await ajaxCall({
                    url: baseUrl + '/home/getHistory',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    let students = res.data.students;

                    if (students.length == 0) {
                        $("#reportContainer").html(`<div class="alert alert-info">No students found in this class</div>`);
                        $('#viewReportBtn').html($('#viewReportBtn').attr('data-content')).css('pointer-events', 'auto');
                        return;
                    }

                    let days = Object.keys(students[0].sessions);
                    
                    $("#reportContainer").html(
                        `
                        <h5>Showing ${weeklyReportType == 'last7days' ? 'last 7 days' : 'week ' + week}</h5>
                        <p class="text-muted mb-3">The following table shows your session percentage for each day</p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    ${days.map(day => `<th>${day}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${students.map(student => `<tr>
                                    <td>${student.username}</td>
                                    ${days.map(day => `<td>${student.sessions[day].percentage ? student.sessions[day].percentage + '%' : '-'}</td>`).join('')}
                                </tr>`).join('')}
                            </tbody>
                        </table>`);
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
            catch (err) {
                new Notify({
                    title: 'Error',
                    text: err.responseJSON.message || 'Something went wrong',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }

            // Reset button
            $('#viewReportBtn').html($('#viewReportBtn').attr('data-content')).css('pointer-events', 'auto');
        });
    });
</script>
<?= $this->endSection() ?>