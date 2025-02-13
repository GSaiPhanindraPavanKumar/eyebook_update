<?php include("sidebar.php"); ?>

<style>
.xp-card {
    background: var(--card-bg) !important;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    color: var(--text-color) !important;
}

.level-info {
    text-align: center;
    margin-bottom: 30px;
}

.level-number {
    font-size: 3em;
    font-weight: bold;
    color: var(--menu-icon) !important;
    margin-bottom: 10px;
}

.stars-container {
    margin: 15px 0;
}

.star {
    color: #ffd700;
    font-size: 1.5em;
    margin: 0 2px;
}

.progress {
    height: 25px;
    background-color: var(--progress-bar-bg) !important;
    border-radius: 12px;
    margin: 20px 0;
    overflow: hidden;
    border: 1px solid var(--border-color) !important;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    background-color: var(--menu-icon) !important;
    transition: width 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff !important;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.stat-card {
    background: var(--card-bg) !important;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid var(--border-color) !important;
}

.stat-value {
    font-size: 1.8em;
    font-weight: bold;
    color: var(--menu-icon) !important;
    margin: 10px 0;
}

.stat-label {
    color: var(--text-color) !important;
    font-size: 0.9em;
}

.next-level-info {
    text-align: center;
    margin-top: 30px;
    padding: 20px;
    background: var(--hover-bg) !important;
    border-radius: 8px;
    color: var(--menu-icon) !important;
}

.text-muted {
    color: var(--text-color) !important;
    opacity: 0.7;
}

/* Card title override */
.card-title {
    color: var(--text-color) !important;
}

/* Add these variables to both themes in sidebar.php */
:root {
    /* ... existing variables ... */
    --progress-bar-bg: rgba(75, 73, 172, 0.1);
}

body.dark-theme {
    /* ... existing variables ... */
    --progress-bar-bg: rgba(110, 168, 254, 0.1);
}
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Experience Points & Levels</h4>
                        
                        <div class="xp-card">
                            <div class="level-info">
                                <div class="level-number">
                                    Level <?php echo $userData['level']; ?>
                                </div>
                                
                                <?php if ($stars = floor($userData['level'] / 10)): ?>
                                    <div class="stars-container">
                                        <?php for($i = 0; $i < $stars; $i++): ?>
                                            <i class="fas fa-star star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="text-muted">
                                        <?php echo $stars; ?> <?php echo $stars === 1 ? 'Star' : 'Stars'; ?> Earned
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php
                            $currentXP = $userData['xp'];
                            $currentLevel = $userData['level'];
                            $nextLevelXP = ($currentLevel + 1) * 100;
                            $progress = ($currentXP % 100);
                            $progressPercent = ($progress / 100) * 100;
                            ?>
                            
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo $progressPercent; ?>%"
                                     aria-valuenow="<?php echo $progress; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?php echo $progress; ?>/100 XP
                                </div>
                            </div>

                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo $currentXP; ?></div>
                                    <div class="stat-label">Total XP</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo $stars; ?></div>
                                    <div class="stat-label">Stars Earned</div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo $nextLevelXP - $currentXP; ?></div>
                                    <div class="stat-label">XP to Next Level</div>
                                </div>
                            </div>

                            <div class="next-level-info">
                                <h5>Next Milestone</h5>
                                <?php
                                $nextStar = (floor($currentLevel / 10) + 1) * 10;
                                $levelsToStar = $nextStar - $currentLevel;
                                ?>
                                <p>
                                    <?php echo $levelsToStar; ?> more levels until your next star at Level <?php echo $nextStar; ?>!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.html"); ?>
</div> 