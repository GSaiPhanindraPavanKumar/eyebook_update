<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Support Tickets</h3>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="justify-content-end d-flex">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#newTicketModal">
                                <i class="ti-plus"></i> New Ticket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="ticketTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="active-tab" data-toggle="tab" href="#active" role="tab">
                    Active Tickets
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                    Closed Tickets
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="ticketTabContent">
            <!-- Active Tickets -->
            <div class="tab-pane fade show active" id="active" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Replies</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($activeTickets)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="p-3">
                                                <i class="ti-ticket text-muted mb-2" style="font-size: 2em;"></i>
                                                <p class="text-muted">No active tickets found</p>
                                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newTicketModal">
                                                    Create New Ticket
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php for ($i = 0; $i < count($activeTickets); $i++): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activeTickets[$i]['ticket_number']); ?></td>
                                            <td><?php echo htmlspecialchars($activeTickets[$i]['subject']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($activeTickets[$i]['created_at'])); ?></td>
                                            <td><?php echo $activeTickets[$i]['reply_count']; ?></td>
                                            <td>
                                                <a href="/student/view_ticket/<?php echo $activeTickets[$i]['id']; ?>" class="btn btn-info btn-sm">
                                                    View Ticket
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endfor; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Closed Tickets -->
            <div class="tab-pane fade" id="closed" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Closed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($closedTickets)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="p-3">
                                                <i class="ti-ticket text-muted mb-2" style="font-size: 2em;"></i>
                                                <p class="text-muted">No closed tickets found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($closedTickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($ticket['closed_at'])); ?></td>
                                            <td>
                                                <a href="/student/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
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
    </div>
</div>

<!-- Update the New Ticket Modal -->
<div class="modal fade prevent-redirect" id="newTicketModal" tabindex="-1" role="dialog" aria-labelledby="newTicketModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTicketModalLabel">Create New Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newTicketForm" onsubmit="createTicket(event)">
                    <div class="form-group">
                        <label for="ticketSubject">Subject</label>
                        <input type="text" class="form-control" id="ticketSubject" required>
                    </div>
                    <div class="form-group">
                        <label for="ticketDescription">Description</label>
                        <textarea class="form-control" id="ticketDescription" rows="4" required></textarea>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update the Ticket View Modal -->
<div class="modal fade prevent-redirect" id="viewTicketModal" tabindex="-1" role="dialog" aria-labelledby="viewTicketModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTicketModalLabel">Ticket Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="ticketDetails"></div>
                <div id="ticketReplies" class="mt-4"></div>
                <div id="replyForm" class="mt-4"></div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script>
$(document).ready(function() {
    // Prevent default link behavior for the entire modal
    $('.modal a').on('click', function(e) {
        e.preventDefault();
    });

    // Handle modal backdrop clicks
    $('.modal').on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // Prevent form inputs from triggering navigation
    $('.modal input, .modal textarea').on('click focus', function(e) {
        e.stopPropagation();
        return true; // Allow default input behavior
    });

    // Initialize modals
    $('#newTicketModal, #viewTicketModal').modal({
        show: false,
        backdrop: 'static',
        keyboard: false
    });

    // Handle view ticket button clicks
    $(document).on('click', '.btn-info[onclick*="viewTicket"]', function(e) {
        e.preventDefault();
        const ticketId = $(this).attr('onclick').match(/\d+/)[0];
        viewTicket(ticketId);
    });

    // Handle new ticket button click
    $('.btn-primary[data-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        $('#newTicketModal').modal('show');
    });
});

// Update the createTicket function
function createTicket(event) {
    // Add a flag to prevent double submission
    if ($('#newTicketForm').data('submitting')) {
        return;
    }
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const subject = $('#ticketSubject').val().trim();
    const description = $('#ticketDescription').val().trim();
    
    if (!subject || !description) {
        alert('Please fill in all fields');
        return;
    }
    
    // Set the submitting flag
    $('#newTicketForm').data('submitting', true);
    
    $.ajax({
        url: '/student/create_ticket',
        method: 'POST',
        data: {
            subject: subject,
            description: description
        },
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.success) {
                    $('#newTicketModal').modal('hide');
                    $('#ticketSubject').val('');
                    $('#ticketDescription').val('');
                    alert('Ticket created successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Error creating ticket');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('An error occurred while creating the ticket');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred while creating the ticket');
        },
        complete: function() {
            // Reset the submitting flag
            $('#newTicketForm').data('submitting', false);
        }
    });
}

// Update the viewTicket function
function viewTicket(ticketId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    $.ajax({
        url: '/student/get_ticket_details/' + ticketId,
        method: 'GET',
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                const ticket = data.ticket;
                const replies = data.replies;

                // Display ticket details with status badge
                let statusBadgeClass = ticket.status === 'active' ? 'badge-success' : 'badge-secondary';
                let detailsHtml = `
                    <div class="ticket-header d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Ticket #${ticket.ticket_number}</h6>
                            <p class="text-muted mb-1">Created on ${new Date(ticket.created_at).toLocaleString()}</p>
                        </div>
                        <span class="badge ${statusBadgeClass}">${ticket.status.toUpperCase()}</span>
                    </div>
                    <div class="ticket-subject">
                        <h5>${ticket.subject}</h5>
                    </div>
                    <div class="ticket-description card bg-light">
                        <div class="card-body">
                            ${ticket.description}
                        </div>
                    </div>
                `;

                // Display replies
                let repliesHtml = '<div class="ticket-replies">';
                if (replies.length === 0) {
                    repliesHtml += `
                        <div class="text-center text-muted p-3">
                            <i class="ti-comments" style="font-size: 2em;"></i>
                            <p class="mt-2">No replies yet</p>
                        </div>
                    `;
                } else {
                    replies.forEach(reply => {
                        const replyClass = reply.user_role === 'student' ? 'student-reply' : 'staff-reply';
                        const roleClass = reply.user_role === 'student' ? 'text-primary' : 'text-success';
                        repliesHtml += `
                            <div class="reply ${replyClass}">
                                <div class="reply-header">
                                    <strong>${reply.user_name}</strong>
                                    <span class="badge badge-light ${roleClass}">${reply.user_role}</span>
                                    <small class="text-muted">${new Date(reply.created_at).toLocaleString()}</small>
                                </div>
                                <div class="reply-content">
                                    ${reply.message}
                                </div>
                            </div>
                        `;
                    });
                }
                repliesHtml += '</div>';

                $('#ticketDetails').html(detailsHtml);
                $('#ticketReplies').html(repliesHtml);

                // Show reply form only for active tickets
                if (ticket.status === 'active') {
                    $('#replyForm').html(`
                        <form onsubmit="submitReply(event, ${ticket.id})">
                            <div class="form-group">
                                <label>Your Reply</label>
                                <textarea class="form-control" id="replyMessage" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Reply</button>
                        </form>
                    `);
                } else {
                    $('#replyForm').html(`
                        <div class="alert alert-info">
                            <i class="ti-info-circle mr-2"></i>
                            This ticket was closed on ${new Date(ticket.closed_at).toLocaleString()}<br>
                            To reopen this issue, please create a new ticket and reference ticket #${ticket.ticket_number}
                        </div>
                    `);
                }

                $('#viewTicketModal').modal('show');
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('An error occurred while viewing the ticket');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred while viewing the ticket');
        }
    });
}

// Update the submitReply function
function submitReply(event, ticketId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const message = $('#replyMessage').val().trim();
    if (!message) {
        alert('Please enter a reply message');
        return;
    }
    
    $.ajax({
        url: '/student/add_ticket_reply',
        method: 'POST',
        data: {
            ticket_id: ticketId,
            message: message
        },
        success: function(response) {
            try {
                // Try to parse if response is JSON string
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.success) {
                    viewTicket(ticketId); // Refresh the ticket view
                } else {
                    alert(data.message || 'Error adding reply');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('An error occurred while adding the reply');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred while adding the reply');
        }
    });
}
</script>

<style>
.ticket-replies {
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
}

.reply {
    margin-bottom: 15px;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.student-reply {
    background-color: #f8f9fa;
    margin-left: 40px;
    border-left: 4px solid #007bff;
}

.staff-reply {
    background-color: #e3f2fd;
    margin-right: 40px;
    border-left: 4px solid #28a745;
}

.reply-header {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reply-content {
    margin-left: 10px;
    white-space: pre-wrap;
}

.ticket-description {
    margin: 15px 0;
}

.badge {
    padding: 5px 10px;
    font-size: 12px;
}

.text-primary {
    color: #007bff !important;
}

.text-success {
    color: #28a745 !important;
}

/* Prevent text selection in modals */
.modal {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Allow text selection in form inputs and content */
.modal input,
.modal textarea,
.ticket-description,
.reply-content {
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}

/* Ensure modal stays above other content */
.modal {
    z-index: 1050;
}

.modal-dialog {
    z-index: 1051;
}

.modal-content {
    z-index: 1052;
}

.modal-backdrop {
    z-index: 1049;
}

/* Allow text input */
.modal input,
.modal textarea {
    z-index: 1053;
    position: relative;
    cursor: text !important;
}

/* Remove pointer-events styles */
.modal,
.modal-dialog,
.modal-content,
.modal input,
.modal textarea,
.modal button {
    pointer-events: auto;
}

/* Allow text selection in inputs */
.modal input,
.modal textarea {
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
}

/* Add these new styles */
.prevent-redirect {
    pointer-events: auto !important;
}

.prevent-redirect .modal-content {
    pointer-events: auto !important;
}

.modal-open {
    overflow: hidden;
    height: 100vh;
}

.modal {
    pointer-events: auto;
}

.modal-dialog {
    pointer-events: auto;
}

.modal-content {
    pointer-events: auto;
}

/* Remove pointer-events from form elements since they're now handled by event delegation */
.modal input,
.modal textarea,
.modal button,
.modal select {
    pointer-events: auto;
}

/* Update text selection styles */
.modal-content {
    user-select: text;
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
}

/* Remove !important from pointer-events */
.prevent-redirect,
.prevent-redirect .modal-content {
    pointer-events: auto;
}
</style> 