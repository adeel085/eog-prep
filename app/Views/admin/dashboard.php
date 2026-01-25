<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('head') ?>
<style>
    .class-list-item {
        cursor: pointer;
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .class-list-item:hover {
        background-color: #f8f9fa;
    }
    .class-list-item.active {
        background-color: #e7f1ff;
        border-left: 3px solid #0d6efd;
    }
    .class-list-item:last-child {
        border-bottom: none;
    }
    .class-name {
        font-weight: 500;
        color: #495057;
    }
    .student-count {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .topic-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    .topic-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 15px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .topic-header:hover {
        opacity: 0.95;
    }
    .topic-header h6 {
        margin: 0;
        font-weight: 600;
    }
    .topic-header .toggle-icon {
        transition: transform 0.3s ease;
    }
    .topic-header.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
    .topic-content {
        padding: 0;
    }
    .level-section {
        border-bottom: 1px solid #e9ecef;
    }
    .level-section:last-child {
        border-bottom: none;
    }
    .level-header {
        background-color: #f8f9fa;
        padding: 10px 15px;
        font-weight: 600;
        color: #495057;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .level-header .level-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .level-indicator {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }
    .level-indicator.level-1 { background-color: #28a745; }
    .level-indicator.level-2 { background-color: #ffc107; color: #333; }
    .level-indicator.level-3 { background-color: #dc3545; }
    .student-list {
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .student-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .student-item:last-child {
        border-bottom: none;
    }
    .student-item:nth-child(odd) {
        background-color: #fafafa;
    }
    .student-rank {
        min-width: 24px;
        font-size: 13px;
        font-weight: 600;
        margin-right: 10px;
        color: #6c757d;
    }
    .student-name {
        flex-grow: 1;
        font-weight: 500;
    }
    .student-score {
        font-weight: bold;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.875rem;
    }
    .student-score.excellent { background-color: #d4edda; color: #155724; }
    .student-score.good { background-color: #fff3cd; color: #856404; }
    .student-score.needs-work { background-color: #f8d7da; color: #721c24; }
    .no-students {
        padding: 15px;
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    .loading-spinner {
        text-align: center;
        padding: 40px;
        width: 100%;
    }
    .loading-spinner .spinner-border {
        display: block;
        margin: 0 auto 10px;
    }
    .students-count-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 10px;
        background-color: rgba(255,255,255,0.2);
    }
    .visually-hidden {
        display: none;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Dashboard</h4>
            <div class="page-title-right">
                <span id="selectedClassName" class="text-muted"></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Classes Sidebar -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Classes</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($classes)): ?>
                    <div class="empty-state">
                        <i class="ri-folder-line"></i>
                        <p>No classes found</p>
                    </div>
                <?php else: ?>
                    <div class="class-list">
                        <?php foreach ($classes as $class): ?>
                            <div class="class-list-item" data-class-id="<?= $class['id'] ?>" data-class-name="<?= htmlspecialchars($class['name']) ?>">
                                <div class="class-name"><?= htmlspecialchars($class['name']) ?></div>
                                <div class="student-count">
                                    <i class="ri-user-line"></i> <?= count($class['students']) ?> student<?= count($class['students']) != 1 ? 's' : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Topics Report Area -->
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Topics Performance</h5>
                <span id="studentsCount" class="badge bg-info" style="display: none;"></span>
            </div>
            <div class="card-body">
                <div id="topicsReportContainer">
                    <div class="empty-state">
                        <i class="ri-bar-chart-box-line"></i>
                        <p>Select a class to view topics performance</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {
        const classes = <?= json_encode($classes) ?>;

        // Class click handler
        $('.class-list-item').click(async function() {
            const classId = $(this).data('class-id');
            const className = $(this).data('class-name');

            // Update active state
            $('.class-list-item').removeClass('active');
            $(this).addClass('active');

            // Update header
            $('#selectedClassName').text(className);

            // Show loading
            $('#topicsReportContainer').html(`
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading topics...</p>
                </div>
            `);

            try {
                let formData = new FormData();
                formData.append('class_id', classId);

                const result = await ajaxCall({
                    url: baseUrl + 'admin/dashboard/class-topics-report',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (result.status === 'success') {
                    renderTopicsReport(result.data);
                } else {
                    showError(result.message || 'Failed to load topics');
                }
            } catch (error) {
                console.error('Error loading topics:', error);
                showError('An error occurred while loading topics');
            }
        });

        function renderTopicsReport(data) {
            const { topics, students_count } = data;

            // Update students count badge
            $('#studentsCount').text(`${students_count} student${students_count != 1 ? 's' : ''}`).show();

            if (topics.length === 0) {
                $('#topicsReportContainer').html(`
                    <div class="empty-state">
                        <i class="ri-file-list-line"></i>
                        <p>No topics found for this class</p>
                    </div>
                `);
                return;
            }

            let html = '<div class="topics-list">';

            topics.forEach((topic, index) => {
                html += `
                    <div class="topic-card">
                        <div class="topic-header" data-topic-id="${topic.id}">
                            <h6>${topic.name}</h6>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="topic-content" id="topic-content-${topic.id}">
                            ${renderLevels(topic.levels)}
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            $('#topicsReportContainer').html(html);

            // Handle collapse toggle
            $('.topic-header').on('click', function() {
                const topicId = $(this).data('topic-id');
                $(this).toggleClass('collapsed');
                $(`#topic-content-${topicId}`).slideToggle(200);
            });
        }

        function renderLevels(levels) {
            let html = '';
            
            for (let level = 1; level <= 3; level++) {
                const students = levels[level] || [];
                
                html += `
                    <div class="level-section">
                        <div class="level-header">
                            <div class="level-title">
                                <span class="level-indicator level-${level}">${level}</span>
                                <span>Level ${level}</span>
                            </div>
                            <span class="students-count-badge bg-secondary text-white">
                                ${students.length} student${students.length != 1 ? 's' : ''}
                            </span>
                        </div>
                        ${students.length > 0 ? renderStudentsList(students) : '<div class="no-students">No students have completed this level yet</div>'}
                    </div>
                `;
            }
            
            return html;
        }

        function renderStudentsList(students) {
            let html = '<ul class="student-list">';
            
            students.forEach((student, index) => {
                const rank = index + 1;

                let scoreClass = 'needs-work';
                if (student.score >= 80) scoreClass = 'excellent';
                else if (student.score >= 60) scoreClass = 'good';

                html += `
                    <li class="student-item">
                        <div class="d-flex align-items-center">
                            <span class="student-rank">${rank}.</span>
                            <span class="student-name">${student.student_name}</span>
                        </div>
                        <span class="student-score ${scoreClass}">${student.score}%</span>
                    </li>
                `;
            });
            
            html += '</ul>';
            return html;
        }

        function showError(message) {
            $('#topicsReportContainer').html(`
                <div class="empty-state text-danger">
                    <i class="ri-error-warning-line"></i>
                    <p>${message}</p>
                </div>
            `);
        }

        // Auto-select first class if available
        if (classes.length > 0) {
            $('.class-list-item').first().click();
        }
    });
</script>
<?= $this->endSection() ?>