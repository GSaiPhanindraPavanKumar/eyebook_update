<?php include('sidebar-content.php'); ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Submit Assignment
            </h1>
        </div>

        <!-- Assignment Submit Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <!-- Assignment Details -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                        <?php echo htmlspecialchars($assignment['title'] ?? ''); ?>
                    </h2>
                    
                    <!-- Uncomment if you want to show instructions and due date
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Instructions</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo nl2br(htmlspecialchars($assignment['instructions'] ?? '')); ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</h3>
                            <p class="mt-1 text-gray-900 dark:text-gray-300">
                                <?php echo htmlspecialchars($assignment['due_date'] ?? ''); ?>
                            </p>
                        </div>
                    </div>
                    -->
                </div>

                <!-- Messages/Alerts -->
                <?php if (!empty($messages)): ?>
                    <div class="mb-6">
                        <?php foreach ($messages as $message): ?>
                            <div class="p-4 bg-blue-100 text-blue-700 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">
                        Submit Your Assignment
                    </h3>
                    
                    <form action="/student/submit_assignment/<?php echo $assignment['id']; ?>" 
                          method="post" 
                          enctype="multipart/form-data"
                          class="space-y-6">
                        
                        <div>
                            <label for="assignment_file" 
                                   class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Upload Assignment File
                            </label>
                            <div class="mt-1 flex items-center">
                                <input type="file" 
                                       id="assignment_file" 
                                       name="assignment_file"
                                       required
                                       class="block w-full text-sm text-gray-500 dark:text-gray-400
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-md file:border-0
                                              file:text-sm file:font-medium
                                              file:bg-primary file:text-white
                                              hover:file:cursor-pointer hover:file:bg-primary-hover
                                              dark:file:bg-primary dark:file:text-white
                                              dark:hover:file:bg-primary-hover">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                Submit Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">