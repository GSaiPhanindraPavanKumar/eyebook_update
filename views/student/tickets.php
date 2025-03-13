<?php include 'sidebar-content.php'; ?>

<!-- Main Content -->
<div id="main-content" class="main-content-expanded min-h-screen bg-gray-50 dark:bg-gray-900 pt-20">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Header Section -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-4 sm:mb-0">
                Support Tickets
            </h1>
            <button onclick="showNewTicketForm()" 
                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Ticket
            </button>
        </div>

        <!-- Tabs -->
        <div class="mb-8">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('active')" 
                            class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary text-primary">
                        Active Tickets
                    </button>
                    <button onclick="showTab('closed')" 
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                        Closed Tickets
                    </button>
                </nav>
            </div>
        </div>

        <!-- Active Tickets Table -->
        <div id="active-tab" class="tab-content">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">Ticket #</th>
                                <th class="px-4 py-3">Subject</th>
                                <th class="px-4 py-3">Created</th>
                                <th class="px-4 py-3">Replies</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            <?php if (empty($activeTickets)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="mt-2 text-gray-500 dark:text-gray-400">No active tickets found</p>
                                        <button onclick="showNewTicketForm()"
                                                class="mt-4 px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                            Create New Ticket
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activeTickets as $ticket): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-4 py-3">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo $ticket['reply_count']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="/student/view_ticket/<?php echo $ticket['id']; ?>" 
                                               class="px-3 py-1 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                                View Ticket
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Closed Tickets Table -->
        <div id="closed-tab" class="tab-content hidden">
            <!-- Similar structure as active tickets table but with closed tickets data -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">Ticket #</th>
                                <th class="px-4 py-3">Subject</th>
                                <th class="px-4 py-3">Created</th>
                                <th class="px-4 py-3">Closed</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            <?php if (empty($closedTickets)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="mt-2 text-gray-500 dark:text-gray-400">No closed tickets found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($closedTickets as $ticket): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-4 py-3">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                <?php echo date('M d, Y H:i', strtotime($ticket['closed_at'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="/student/view_ticket/<?php echo $ticket['id']; ?>" 
                                               class="px-3 py-1 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover transition-colors">
                                                View Ticket
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Update tab button styles
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-primary', 'text-primary');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Style active tab button
    event.currentTarget.classList.remove('border-transparent', 'text-gray-500');
    event.currentTarget.classList.add('border-primary', 'text-primary');
}

function showNewTicketForm() {
    Swal.fire({
        title: 'Create New Ticket',
        html: `
            <div class="mb-4">
                <label for="swal-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left mb-1">
                    Subject <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="swal-subject" 
                       class="swal2-input" 
                       placeholder="Enter ticket subject"
                       required>
            </div>
            <div>
                <label for="swal-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left mb-1">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea id="swal-description" 
                         class="swal2-textarea" 
                         rows="4" 
                         placeholder="Enter ticket description"
                         required></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Create Ticket',
        confirmButtonColor: '#4B49AC',
        cancelButtonColor: '#d33',
        focusConfirm: false,
        width: '32rem',
        position: 'center',
        customClass: {
            container: 'swal-container',
            popup: 'swal-popup dark:bg-gray-800',
            input: 'dark:bg-gray-700 dark:text-white',
            validationMessage: 'text-sm text-red-500'
        },
        didOpen: () => {
            // Focus on subject input when modal opens
            document.getElementById('swal-subject').focus();
        },
        preConfirm: () => {
            const subject = Swal.getPopup().querySelector('#swal-subject').value.trim();
            const description = Swal.getPopup().querySelector('#swal-description').value.trim();
            
            const errors = [];
            if (!subject) errors.push('Subject is required');
            if (!description) errors.push('Description is required');
            
            if (errors.length > 0) {
                Swal.showValidationMessage(errors.join('<br>'));
                return false;
            }
            
            return { subject, description };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            createTicket(result.value.subject, result.value.description);
        }
    });
}

function createTicket(subject, description) {
    if (!subject || !description) {
        Swal.fire({
            title: 'Error',
            text: 'Subject and description are required',
            icon: 'error',
            confirmButtonColor: '#4B49AC'
        });
        return;
    }

    // Create URLSearchParams object to format data
    const formData = new URLSearchParams();
    formData.append('subject', subject);
    formData.append('description', description);

    fetch('/student/create_ticket', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString() // This will format as subject=value&description=value
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Ticket created successfully',
                icon: 'success',
                confirmButtonColor: '#4B49AC'
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Error creating ticket');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Error creating ticket',
            icon: 'error',
            confirmButtonColor: '#4B49AC'
        });
    });
}
</script>

<style>
/* Custom styles for SweetAlert */
.swal2-input, .swal2-textarea {
    @apply w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white;
    margin: 0 !important;
}

.swal2-popup {
    @apply dark:bg-gray-800 dark:text-white;
    padding: 2rem !important;
}

.swal2-title {
    @apply dark:text-white text-xl font-semibold !important;
}

.swal2-html-container {
    @apply text-left !important;
    margin: 1rem 0 !important;
}

/* Center the modal */
.swal-container {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.swal-popup {
    margin: 0 !important;
}

/* Make sure form inputs are visible in dark mode */
.dark .swal2-input, .dark .swal2-textarea {
    background-color: #374151 !important;
    color: white !important;
}

.dark .swal2-input::placeholder, .dark .swal2-textarea::placeholder {
    color: #9CA3AF !important;
}

/* Ensure proper spacing and sizing */
.swal2-input {
    height: 2.5rem !important;
    margin-top: 0.25rem !important;
}

.swal2-textarea {
    min-height: 6rem !important;
    margin-top: 0.25rem !important;
}
</style> 