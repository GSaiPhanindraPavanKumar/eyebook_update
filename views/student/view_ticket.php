<?php include 'sidebar-content.php'; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?>
            </h1>
            <div class="flex space-x-3">
                <?php if ($ticket['status'] === 'active'): ?>
                    <button onclick="closeTicket(<?php echo $ticket['id']; ?>)" 
                            class="px-4 py-2 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-md hover:bg-yellow-200 dark:bg-yellow-700 dark:text-yellow-100 dark:hover:bg-yellow-600 transition-colors">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Close Ticket
                    </button>
                <?php endif; ?>
                <a href="/student/tickets" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Tickets
                </a>
            </div>
        </div>

        <!-- Ticket Details Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full 
                            <?php echo $ticket['status'] === 'active' 
                                ? 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100' 
                                : 'text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300'; ?>">
                            <?php echo strtoupper($ticket['status']); ?>
                        </span>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Created on <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                        </p>
                    </div>
                    <?php if ($ticket['status'] === 'closed'): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Closed on <?php echo date('M d, Y H:i', strtotime($ticket['closed_at'])); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Subject and Description -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Subject:</h3>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($ticket['subject']); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description:</h3>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-gray-700 dark:text-gray-300">
                            <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replies Section -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Conversation History</h2>
                
                <div class="space-y-6 max-h-[600px] overflow-y-auto px-4 custom-scrollbar">
                    <?php if (empty($replies)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">No replies yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($replies as $reply): ?>
                            <?php 
                            $isStudent = $reply['user_role'] === 'student';
                            ?>
                            <div class="flex <?php echo $isStudent ? 'justify-start' : 'justify-end'; ?>">
                                <div class="max-w-[70%] <?php echo $isStudent 
                                    ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-primary' 
                                    : 'bg-gray-50 dark:bg-gray-700 border-r-4 border-green-500'; ?> 
                                    rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($reply['user_name']); ?>
                                        </span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $isStudent 
                                            ? 'text-primary bg-blue-100 dark:bg-blue-900/40' 
                                            : 'text-green-700 bg-green-100 dark:bg-green-900/40'; ?>">
                                            <?php echo ucfirst($reply['user_role']); ?>
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                        <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($ticket['status'] === 'active'): ?>
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <form id="replyForm" method="POST" action="/student/add_ticket_reply" class="space-y-4">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Your Reply
                                </label>
                                <textarea id="message" name="message" rows="3" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            </div>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                Send Reply
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center text-blue-700 dark:text-blue-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            This ticket is closed. To reopen this issue, please create a new ticket and reference ticket #<?php echo $ticket['ticket_number']; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* For Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function closeTicket(ticketId) {
    Swal.fire({
        title: 'Close Ticket',
        text: 'Are you sure you want to close this ticket?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4B49AC',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, close it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/student/close_ticket', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ticket_id: ticketId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Closed!',
                        text: 'The ticket has been closed.',
                        icon: 'success',
                        confirmButtonColor: '#4B49AC'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Error closing ticket',
                        icon: 'error',
                        confirmButtonColor: '#4B49AC'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Error',
                    text: 'Error closing ticket',
                    icon: 'error',
                    confirmButtonColor: '#4B49AC'
                });
            });
        }
    });
}
</script> 