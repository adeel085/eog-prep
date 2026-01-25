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
        border-left: 4px solid #dee2e6;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }
    .topic-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .topic-card.excellent {
        border-left-color: #28a745;
    }
    .topic-card.good {
        border-left-color: #ffc107;
    }
    .topic-card.needs-work {
        border-left-color: #dc3545;
    }
    .topic-card.no-data {
        border-left-color: #6c757d;
        opacity: 0.7;
    }
    .score-badge {
        font-size: 1.1rem;
        font-weight: bold;
        padding: 8px 15px;
        border-radius: 20px;
    }
    .level-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        margin-right: 5px;
    }
    .level-badge.has-data {
        background-color: #e7f1ff;
        color: #0d6efd;
    }
    .level-badge.no-data {
        background-color: #f8f9fa;
        color: #adb5bd;
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
                const hasData = topic.sessions_count > 0;
                const score = topic.average_score;
                
                // Determine card class based on score
                let cardClass = 'no-data';
                let scoreClass = 'bg-secondary';
                if (hasData) {
                    if (score >= 80) {
                        cardClass = 'excellent';
                        scoreClass = 'bg-success';
                    } else if (score >= 60) {
                        cardClass = 'good';
                        scoreClass = 'bg-warning';
                    } else {
                        cardClass = 'needs-work';
                        scoreClass = 'bg-danger';
                    }
                }

                html += `
                    <div class="card topic-card ${cardClass}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="topic-info flex-grow-1">
                                    <h6 class="mb-1">${topic.name}</h6>
                                    <div class="level-badges mb-2">
                                        ${renderLevelBadges(topic.level_averages)}
                                    </div>
                                    <small class="text-muted">
                                        ${hasData ? `${topic.sessions_count} session${topic.sessions_count != 1 ? 's' : ''} completed` : 'No sessions yet'}
                                    </small>
                                </div>
                                <div class="topic-score text-end">
                                    <span class="score-badge ${scoreClass} text-white">
                                        ${hasData ? score + '%' : 'N/A'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            $('#topicsReportContainer').html(html);
        }

        function renderLevelBadges(levelAverages) {
            let badges = '';
            for (let level = 1; level <= 3; level++) {
                const avg = levelAverages[level];
                const hasData = avg !== null;
                badges += `
                    <span class="level-badge ${hasData ? 'has-data' : 'no-data'}">
                        L${level}: ${hasData ? Math.round(avg) + '%' : '-'}
                    </span>
                `;
            }
            return badges;
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