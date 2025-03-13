<?php include 'sidebar-content.php'; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                View Assignment
            </h1>
            <a href="/student/view_course/<?php echo str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)); ?>" 
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                Back to Course
            </a>
        </div>

        <!-- Assignment Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                    <?php echo htmlspecialchars($assignment['title']); ?>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Start Time:</span> 
                        <?php echo htmlspecialchars($assignment['start_time']); ?>
                    </div>
                    <div class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Due Date:</span> 
                        <?php echo htmlspecialchars($assignment['due_date']); ?>
                    </div>
                </div>

                <?php
                $current_time = new DateTime();
                $current_time->modify('+5 hours +30 minutes');
                $start_time = new DateTime($assignment['start_time']);
                $due_date = new DateTime($assignment['due_date']);
                
                if ($current_time < $start_time): ?>
                    <div class="p-4 bg-yellow-100 text-yellow-700 rounded-lg dark:bg-yellow-900/20 dark:text-yellow-400">
                        <strong>Status:</strong> The assignment has not yet started.
                    </div>
                <?php else: ?>
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</h3>
                        <p class="text-gray-700 dark:text-gray-300">
                            <?php echo htmlspecialchars($assignment['description']); ?>
                        </p>
                    </div>

                    <?php if (!empty($assignment['file_content'])): ?>
                        <div class="mb-6">
                            <button onclick="toggleFileContent()" 
                                    id="viewButton"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                View File
                            </button>
                            <div id="fileContent" class="hidden mt-4">
                                <embed src="<?php echo htmlspecialchars($assignment['file_content']); ?>" 
                                       type="application/pdf" 
                                       class="w-full h-[600px] rounded-lg">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($student_submission)): ?>
                        <!-- Submission Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Your Submission</h3>
                            
                            <button onclick="toggleSubmissionContent()" 
                                    id="viewSubmissionButton"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors mb-4">
                                View Submission
                            </button>
                            
                            <div id="submissionContent" class="hidden mt-4">
                                <embed src="<?php echo htmlspecialchars($student_submission['file']); ?>" 
                                       type="application/pdf" 
                                       class="w-full h-[600px] rounded-lg">
                            </div>

                            <?php if ($current_time <= $due_date && empty($student_submission['grade']) && empty($student_submission['feedback'])): ?>
                                <form action="/student/delete_submission/<?php echo $assignment['id']; ?>" 
                                      method="post" 
                                      class="mt-4">
                                    <button type="submit" 
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                                        Delete Submission
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($student_submission['grade']) || !empty($student_submission['feedback'])): ?>
                                <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Grading</h4>
                                    <?php if (!empty($student_submission['grade'])): ?>
                                        <p class="text-gray-700 dark:text-gray-300 mb-2">
                                            <span class="font-medium">Grade:</span> 
                                            <?php echo htmlspecialchars($student_submission['grade']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($student_submission['feedback'])): ?>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">Feedback:</span> 
                                            <?php echo htmlspecialchars($student_submission['feedback']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if ($current_time <= $due_date): ?>
                            <!-- Submission Form -->
                            <form action="/student/submit_assignment/<?php echo $assignment['id']; ?>" 
                                  method="post" 
                                  enctype="multipart/form-data" 
                                  class="mt-6">
                                <div class="drag-drop-zone" id="assignmentDragZone">
                                    <input type="file" 
                                           id="submission_file" 
                                           name="submission_file" 
                                           accept="application/pdf" 
                                           class="hidden" 
                                           required>
                                    <div class="text-4xl text-gray-400 dark:text-gray-600 mb-3">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-2">
                                        Drag and drop your PDF file here or 
                                        <span class="text-primary cursor-pointer">browse</span>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500">
                                        Allowed format: PDF only<br>
                                        Maximum file size: 32MB
                                    </p>
                                    <div id="selectedFile" class="hidden mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <span class="file-name text-gray-700 dark:text-gray-300"></span>
                                            <button type="button" class="remove-file text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" 
                                        class="mt-4 px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                    Submit Assignment
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="p-4 bg-red-100 text-red-700 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                                <strong>Status:</strong> The assignment due date has passed. You can no longer submit or delete your submission.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ... Keep existing JavaScript for file handling and toggles ...

// Update toggle functions to use Tailwind classes
function toggleFileContent() {
    var fileContent = document.getElementById('fileContent');
    var viewButton = document.getElementById('viewButton');
    if (fileContent.classList.contains('hidden')) {
        fileContent.classList.remove('hidden');
        viewButton.textContent = 'Hide File';
    } else {
        fileContent.classList.add('hidden');
        viewButton.textContent = 'View File';
    }
}

function toggleSubmissionContent() {
    var submissionContent = document.getElementById('submissionContent');
    var viewSubmissionButton = document.getElementById('viewSubmissionButton');
    if (submissionContent.classList.contains('hidden')) {
        submissionContent.classList.remove('hidden');
        viewSubmissionButton.textContent = 'Hide Submission';
    } else {
        submissionContent.classList.add('hidden');
        viewSubmissionButton.textContent = 'View Submission';
    }
}

// Update file validation to use SweetAlert
function showFileError(message) {
    Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonColor: '#4B49AC'
    });
}

// ... Rest of the file handling JavaScript remains the same ...
</script>

<style>
.drag-drop-zone {
    @apply border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center transition-all cursor-pointer;
}

.drag-drop-zone.dragover {
    @apply border-primary bg-primary/5;
}

#selectedFile {
    word-break: break-all;
}
</style>