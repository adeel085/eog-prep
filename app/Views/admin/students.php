<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Students</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<!-- Clear Last Year Data Confirmation Modal -->
<div id="clearLastYearDataConfirmationModal" class="bs-modal modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<span class="modal-title">Clear Last Year Data</span>
			</div>
			<div class="modal-body">
				<div class="d-flex flex-column align-items-center" style="padding: 0 10px;">
					<div class="mb-2">
						<i class="fa fa-warning text-danger" style="font-size: 50px;"></i>
					</div>

					<p class="text-center">Are you sure you want to clear the last year data? This action cannot be undone.</p>
					
					<?php
					$now = new DateTime();
					$year = (int)$now->format('Y');
					
					if ((int)$now->format('n') < 6) {
						$year -= 1; // go to last year if before June
					}
					
					$firstOfLastJune = new DateTime("$year-06-01");
					?>
					<p class="text-danger text-center" style="font-size: 14px;">This will clear all the students data before <?= $firstOfLastJune->format("F 1, Y") ?></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-danger" id="clearLastYearDataBtn">Yes, Clear</button>
				<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="d-flex flex-wrap justify-content-end align-items-center mb-4" style="gap: 10px;">
                    <div style="flex: 1;">
                        <input type="text" class="form-control form-control-sm" placeholder="Search students by name, username or email" id="search" value="<?= $search ?? '' ?>">
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary" id="searchBtn">Search</button>
                    </div>

                    <?php
                    if ($search) {
                        ?>
                        <div class="w-100">
                            <span>Showing search results for: <strong><?= $search ?></strong></span>
                            <br>
                            <a href="<?= base_url('/admin/students') ?>">Clear search</a>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="d-flex justify-content-end align-items-center mb-4" style="gap: 10px;">
                    <label for="importFile" class="btn btn-sm btn-primary mb-0">
                        <i class="fa fa-file-import"></i> Import Students CSV
                    </label>
                    <input type="file" id="importFile" class="d-none" accept=".csv">

                    <a href="<?= base_url('/admin/students/new') ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> New Student
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Class</th>
                                <th>Grade</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student) : ?>
                                <tr data-id="<?= $student['id'] ?>">
                                    <td><?= $student['full_name'] ?></td>
                                    <td><?= $student['username'] ?></td>
                                    <td><?= $student['email'] ?></td>
                                    <td><?= isset($student['class']) ? $student['class']['name'] : '' ?></td>
                                    <td><?= isset($student['grade']) ? $student['grade']['name'] : '' ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end table-action-btn" style="gap: 10px;">
                                            <a href="<?= base_url('/admin/students/reports/' . $student['id']) ?>" class="table-action-btn" data-toggle="tooltip" title="Reports">
                                                <i class="fa fa-chart-bar"></i>
                                            </a>
                                            <a href="<?= base_url('/admin/students/edit/' . $student['id']) ?>" class="table-action-btn" data-toggle="tooltip" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="table-action-btn delete-btn" data-toggle="tooltip" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($students) == 0) : ?>
                                <tr>
                                    <td colspan="5" class="text-center">No students found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <?= $pager->links() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {

        function searchStudents() {
            let search = $("#search").val();

            if (search.length > 0) {
                window.location.href = baseUrl + 'admin/students?search=' + search;
            }
        }

        $("#importFile").change(async function(e) {

            if ($("#importFile").get(0).files.length == 0) {
                return;
            }

            let formData = new FormData();
            formData.append('importFile', $("#importFile").get(0).files[0]);

            try {
                let res = await ajaxCall({
                    url: baseUrl + 'admin/students/import-csv',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.reload();
                    return;
                }
            }
            catch (error) {
                if (error.status == 401) {
                    new Notify({
                        title: 'Error',
                        text: "Your session has expired. Redirecting to login page...",
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
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

        $("#searchBtn").click(function() {
            searchStudents();
        });

        $("#search").on('keyup', function(event) {
            if (event.keyCode === 13) {
                searchStudents();
            }
        });

        $('.delete-btn').click(async function() {
            
            if (!confirm("Are you sure you want to delete this student?")) {
                return;
            }

            let $tr = $(this).closest('tr');

            let id = $tr.data('id');

            try {

                let formData = new FormData();
                formData.append('id', id);

                let res = await ajaxCall({
                    url: baseUrl + 'admin/students/delete',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.reload();
                    return;
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
                if (error.status == 401) {
                    new Notify({
                        title: 'Error',
                        text: "Your session has expired. Redirecting to login page...",
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
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
    });
</script>
<?= $this->endSection() ?>