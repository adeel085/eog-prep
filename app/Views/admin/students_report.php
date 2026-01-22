<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('head') ?>
<style>
    .level-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .level-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .level-card h6 {
        margin-bottom: 10px;
        color: #495057;
        font-weight: 600;
    }

    .score-display .avg-score {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 5px;
    }

    .score-display .score-details {
        color: #6c757d;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
    }

    .session-item {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 8px;
    }

    .session-item:last-child {
        border-bottom: none !important;
    }

    .badge {
        font-size: 0.875rem;
        padding: 6px 10px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }

    .card-header h5, .card-header h6 {
        color: white;
        margin: 0;
    }

    .toggle-sessions {
        transition: all 0.3s ease;
    }

    .toggle-sessions:hover {
        transform: scale(1.05);
    }

    .topic-chart {
        position: relative;
        height: 150px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $student['full_name'] ?>'s Reports</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Performance Overview</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-primary" id="totalTopics">0</h3>
                            <p class="text-muted mb-0">Topics Attempted</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-success" id="averageScore">0%</h3>
                            <p class="text-muted mb-0">Average Score</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h3 class="text-info" id="totalSessions">0</h3>
                            <p class="text-muted mb-0">Total Sessions</p>
                        </div>
                    </div>
                </div>

                <!-- Overall Performance Chart -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Overall Performance by Level</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="overallChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Topic-wise Performance -->
    <div class="col-12">
        <!-- Topics will be rendered here -->
         <div id="topicsContainer" class="row">

         </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(() => {
        const topics = <?= json_encode($topics) ?>;

        // Calculate overall statistics
        let totalTopics = 0;
        let totalScore = 0;
        let totalSessions = 0;
        let levelScores = {1: [], 2: [], 3: []};

        topics.forEach(topic => {
            let topicHasResults = false;
            let topicScore = 0;
            let topicSessions = 0;

            // Process each level
            Object.keys(topic.results).forEach(level => {
                const results = topic.results[level];
                if (results && results.length > 0) {
                    topicHasResults = true;
                    results.forEach(result => {
                        const score = parseFloat(result.percentage);
                        levelScores[level].push(score);
                        topicScore += score;
                        topicSessions++;
                        totalSessions++;
                    });
                }
            });

            if (topicHasResults) {
                totalTopics++;
                totalScore += topicScore / topicSessions; // Average score for this topic
            }
        });

        // Update overview stats
        $('#totalTopics').text(totalTopics);
        $('#totalSessions').text(totalSessions);
        $('#averageScore').text(totalTopics > 0 ? Math.round(totalScore / totalTopics) + '%' : '0%');

        // Create overall performance chart
        createOverallChart(levelScores);

        // Render topic performance cards
        renderTopicCards(topics);
    });

    function createOverallChart(levelScores) {
        const ctx = document.getElementById('overallChart').getContext('2d');

        const levelLabels = ['Level 1', 'Level 2', 'Level 3'];
        const levelData = [
            levelScores[1].length > 0 ? calculateAverage(levelScores[1]) : 0,
            levelScores[2].length > 0 ? calculateAverage(levelScores[2]) : 0,
            levelScores[3].length > 0 ? calculateAverage(levelScores[3]) : 0
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: levelLabels,
                datasets: [{
                    label: 'Average Score (%)',
                    data: levelData,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function renderTopicCards(topics) {
        const container = $('#topicsContainer');

        topics.forEach(topic => {
            // Check if topic has any results
            let hasResults = false;
            Object.values(topic.results).forEach(results => {
                if (results && results.length > 0) hasResults = true;
            });

            if (!hasResults) return; // Skip topics with no results

            const topicCard = $(`
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">${topic.name}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="level-card level-1">
                                        <h6>Level 1</h6>
                                        <div class="level-stats" data-level="1"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="level-card level-2">
                                        <h6>Level 2</h6>
                                        <div class="level-stats" data-level="2"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="level-card level-3">
                                        <h6>Level 3</h6>
                                        <div class="level-stats" data-level="3"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="topic-chart mb-3">
                                <canvas id="topicChart-${topic.id}" height="80"></canvas>
                            </div>

                            <div class="session-details">
                                <button class="btn btn-sm btn-outline-primary toggle-sessions" data-topic="${topic.id}">
                                    Show Session Details
                                </button>
                                <div class="sessions-list mt-3" id="sessions-${topic.id}" style="display: none;">
                                    <!-- Session details will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);

            container.append(topicCard);

            // Populate level stats and create topic chart
            populateLevelStats(topicCard, topic);
            createTopicChart(topic);

            // Add session toggle functionality
            topicCard.find('.toggle-sessions').click(function() {
                const topicId = $(this).data('topic');
                const sessionsList = $(`#sessions-${topicId}`);
                sessionsList.slideToggle();
                $(this).text(sessionsList.is(':visible') ? 'Hide Session Details' : 'Show Session Details');
            });
        });
    }

    function populateLevelStats(card, topic) {
        for (let level = 1; level <= 3; level++) {
            const levelStats = card.find(`.level-stats[data-level="${level}"]`);
            const results = topic.results[level] || [];

            if (results.length === 0) {
                levelStats.html('<span class="text-muted">Not attempted</span>');
                continue;
            }

            const scores = results.map(r => parseFloat(r.percentage));
            const avgScore = calculateAverage(scores);
            const bestScore = Math.max(...scores);
            const attempts = results.length;

            levelStats.html(`
                <div class="score-display">
                    <div class="avg-score">${avgScore.toFixed(1)}%</div>
                    <div class="score-details">
                        <small>Best: ${bestScore.toFixed(1)}% | Attempts: ${attempts}</small>
                    </div>
                </div>
                <div class="progress mt-2">
                    <div class="progress-bar ${getProgressBarClass(avgScore)}"
                         role="progressbar"
                         style="width: ${avgScore}%"
                         aria-valuenow="${avgScore}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
            `);

            // Populate session details
            const sessionsList = card.find(`#sessions-${topic.id}`);
            results.forEach(result => {
                const sessionDate = formatDate(result.created_at);
                sessionsList.append(`
                    <div class="session-item d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <strong>Level ${level}</strong>
                            <br>
                            <small class="text-muted">${sessionDate}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge ${getScoreBadgeClass(parseFloat(result.percentage))}">${result.percentage}%</span>
                        </div>
                    </div>
                `);
            });
        }
    }

    function createTopicChart(topic) {
        const ctx = document.getElementById(`topicChart-${topic.id}`).getContext('2d');

        const levelData = [1, 2, 3].map(level => {
            const results = topic.results[level] || [];
            return results.length > 0 ? calculateAverage(results.map(r => parseFloat(r.percentage))) : 0;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Level 1', 'Level 2', 'Level 3'],
                datasets: [{
                    label: 'Average Score',
                    data: levelData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function calculateAverage(scores) {
        return scores.reduce((a, b) => a + b, 0) / scores.length;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }

    function getProgressBarClass(score) {
        if (score >= 80) return 'bg-success';
        if (score >= 60) return 'bg-warning';
        return 'bg-danger';
    }

    function getScoreBadgeClass(score) {
        if (score >= 80) return 'bg-success';
        if (score >= 60) return 'bg-warning';
        return 'bg-danger';
    }
</script>
<?= $this->endSection() ?>