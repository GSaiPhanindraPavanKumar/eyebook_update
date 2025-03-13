<?php include("sidebar-content.php"); ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- XP Status Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-8">
                    Experience Points & Levels
                </h2>

                <!-- Level Info -->
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold text-primary dark:text-primary mb-4">
                        Level <?php echo $userData['level']; ?>
                    </div>

                    <?php if ($stars = floor($userData['level'] / 10)): ?>
                        <div class="flex justify-center items-center space-x-1 mb-2">
                            <?php for($i = 0; $i < $stars; $i++): ?>
                                <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
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

                <!-- Progress Bar -->
                <div class="relative pt-1 mb-8">
                    <div class="flex mb-2 items-center justify-between">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Progress to Next Level
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <?php echo $progress; ?>/100 XP
                        </div>
                    </div>
                    <div class="overflow-hidden h-6 bg-gray-200 dark:bg-gray-700 rounded-full">
                        <div class="h-full bg-primary rounded-full transition-all duration-500"
                             style="width: <?php echo $progressPercent; ?>%">
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total XP -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-primary dark:text-primary mb-2">
                            <?php echo $currentXP; ?>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Total XP
                        </div>
                    </div>

                    <!-- Stars Earned -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-primary dark:text-primary mb-2">
                            <?php echo $stars; ?>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Stars Earned
                        </div>
                    </div>

                    <!-- XP to Next Level -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-primary dark:text-primary mb-2">
                            <?php echo $nextLevelXP - $currentXP; ?>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            XP to Next Level
                        </div>
                    </div>
                </div>

                <!-- Next Milestone -->
                <?php
                $nextStar = (floor($currentLevel / 10) + 1) * 10;
                $levelsToStar = $nextStar - $currentLevel;
                ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
                    <h3 class="text-xl font-semibold text-primary dark:text-primary mb-2">
                        Next Milestone
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        <?php echo $levelsToStar; ?> more levels until your next star at Level <?php echo $nextStar; ?>!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 