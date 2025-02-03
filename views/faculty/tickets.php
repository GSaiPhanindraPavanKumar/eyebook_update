<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12">
                        <h3 class="font-weight-bold">Support Tickets</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="ticketTabs" role="tablist">
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
                            <table class="table table-hover" id="activeTicketsTable">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Replies</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($activeTickets)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="p-3">
                                                    <i class="ti-ticket text-muted mb-2" style="font-size: 2em;"></i>
                                                    <p class="text-muted">No active tickets found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($activeTickets as $ticket): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td><?php echo $ticket['reply_count']; ?></td>
                                                <td>
                                                    <a href="/faculty/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
                                                        View Ticket
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Showing all active tickets</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Closed Tickets -->
            <div class="tab-pane fade" id="closed" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="closedTicketsTable">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Closed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($closedTickets)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
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
                                                <td><?php echo htmlspecialchars($ticket['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($ticket['closed_at'])); ?></td>
                                                <td>
                                                    <a href="/faculty/view_ticket/<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">
                                                        View History
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Showing all closed tickets</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include ('footer.html'); ?>
</div>

<!-- Ticket View Modal -->
<div class="modal fade" id="viewTicketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ticket Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="ticketDetails"></div>
                <div id="ticketReplies" class="mt-4"></div>
                <div id="replyForm" class="mt-4"></div>
            </div>
            <div class="modal-footer" id="ticketActions"></div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

<script>
$(document).ready(function() {
    var activeTable = $('#activeTicketsTable').DataTable({
        "order": [[3, "desc"]], // Sort by created date
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ tickets",
            "infoEmpty": "Showing 0 to 0 of 0 tickets",
            "emptyTable": "No tickets available",
            "search": "Search tickets:",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    
    var closedTable = $('#closedTicketsTable').DataTable({
        "order": [[4, "desc"]], // Sort by closed date
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ tickets",
            "infoEmpty": "Showing 0 to 0 of 0 tickets",
            "emptyTable": "No tickets available",
            "search": "Search tickets:",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
});

function viewTicket(ticketId) {
    $.ajax({
        url: '/faculty/get_ticket_details/' + ticketId,
        method: 'GET',
        success: function(response) {
            const data = JSON.parse(response);
            const ticket = data.ticket;
            const replies = data.replies;
            const canClose = data.canClose;

            // Display ticket details
            let detailsHtml = `
                <div class="ticket-header">
                    <h6>Ticket #${ticket.ticket_number}</h6>
                    <p class="text-muted">Created by ${ticket.student_name} on ${new Date(ticket.created_at).toLocaleString()}</p>
                </div>
                <div class="ticket-subject">
                    <h5>${ticket.subject}</h5>
                </div>
                <div class="ticket-description">
                    <p>${ticket.description}</p>
                </div>
            `;
            $('#ticketDetails').html(detailsHtml);

            // Display replies
            let repliesHtml = '<div class="ticket-replies">';
            replies.forEach(reply => {
                repliesHtml += `
                    <div class="reply ${reply.user_role === 'student' ? 'student-reply' : 'staff-reply'}">
                        <div class="reply-header">
                            <strong>${reply.user_name}</strong>
                            <span class="text-muted">${new Date(reply.created_at).toLocaleString()}</span>
                        </div>
                        <div class="reply-content">
                            <p>${reply.message}</p>
                        </div>
                    </div>
                `;
            });
            repliesHtml += '</div>';
            $('#ticketReplies').html(repliesHtml);

            // Show reply form and close button for active tickets
            if (ticket.status === 'active') {
                $('#replyForm').html(`
                    <form onsubmit="submitReply(event, ${ticketId})">
                        <div class="form-group">
                            <label>Your Reply</label>
                            <textarea class="form-control" id="replyMessage" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Reply</button>
                    </form>
                `);
                
                // Show close button only if faculty has replied
                if (canClose) {
                    $('#ticketActions').html(`
                        <button class="btn btn-warning" onclick="closeTicket(${ticketId})">
                            Close Ticket
                        </button>
                    `);
                }
            } else {
                $('#replyForm').html(`
                    <div class="alert alert-info">
                        This ticket was closed on ${new Date(ticket.closed_at).toLocaleString()}
                    </div>
                `);
                $('#ticketActions').html('');
            }

            $('#viewTicketModal').modal('show');
        }
    });
}

function submitReply(event, ticketId) {
    event.preventDefault();
    const message = $('#replyMessage').val();
    
    $.ajax({
        url: '/faculty/add_ticket_reply',
        method: 'POST',
        data: {
            ticket_id: ticketId,
            message: message
        },
        success: function(response) {
            if (response.success) {
                viewTicket(ticketId); // Refresh the ticket view
            }
        }
    });
}

function closeTicket(ticketId) {
    if (confirm('Are you sure you want to close this ticket?')) {
        $.ajax({
            url: '/faculty/close_ticket',
            method: 'POST',
            data: { ticket_id: ticketId },
            success: function(response) {
                if (response.success) {
                    $('#viewTicketModal').modal('hide');
                    location.reload(); // Refresh the page to update tables
                }
            }
        });
    }
}
</script>

<style>
/* Card styles */
.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border: none;
    margin-bottom: 30px;
    background: #fff;
}

.card-body {
    padding: 0;
}

/* Table container */
.table-responsive {
    padding: 0;
    background: transparent;
    box-shadow: none;
    border-radius: 0;
}

/* Table styles */
.table {
    width: 100%;
    margin-bottom: 0;
    color: #212529;
    border-collapse: collapse;
    background: transparent;
}

/* Table header */
.table thead th {
    padding: 20px 15px;
    background-color: #f8f9fa;
    font-weight: 600;
    color: #1F1F1F;
    border-bottom: 2px solid #dee2e6;
}

/* Table rows */
.table tbody tr {
    background-color: #fff;
    border-bottom: 6px solid #f8f9fa;
}

.table tbody td {
    padding: 25px 15px;
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
    color: #6c757d;
    font-size: 14px;
    line-height: 1.6;
}

/* DataTables wrapper */
.dataTables_wrapper {
    padding: 20px;
}

/* Top section with search only */
.top {
    padding: 0 0 15px 0;
}

/* Search box container */
.dataTables_filter {
    text-align: left;
    margin-bottom: 20px;
}

.dataTables_filter input {
    width: 250px;
    margin-left: 0;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    background-color: #f8f9fa;
}

.dataTables_filter input:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

/* Bottom section with length menu and pagination */
.bottom {
    padding: 15px 0 0 0;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: transparent;
}

/* Length menu at bottom */
.dataTables_length {
    order: 1;
    margin: 0;
    white-space: nowrap;
}

/* Info and pagination */
.dataTables_info {
    order: 2;
}

.dataTables_paginate {
    order: 3;
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dataTables_paginate .paginate_button {
    padding: 6px 12px;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    color: #4b49ac !important;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 32px;
    text-align: center;
    font-size: 14px;
    line-height: 1.5;
    color: #fff;
}

.paginate_button.current {
    color: #fff;
    border-radius: 20px;
}

.dataTables_paginate .paginate_button.current {
    background: #4b49ac !important;
    color: #fff;
    border-color: #4b49ac;
    font-weight: 500;
    font-color: #fff;
}

.dataTables_paginate .paginate_button:hover:not(.current) {
    background: #f8f9fa !important;
    color: #fffff !important;
    border-color: #4b49ac;

}

.dataTables_paginate .paginate_button.disabled {
    color: #c0c0c0 !important;
    cursor: not-allowed;
    background: #f8f9fa !important;
    border-color: #dee2e6;
}

.dataTables_paginate .paginate_button.previous,
.dataTables_paginate .paginate_button.next {
    padding: 6px 12px;
}

/* Info text styling */
.dataTables_info {
    color: #6c757d;
    font-size: 14px;
}

/* Update length menu styling */
.dataTables_length select {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 24px 8px 8px;
    margin: 0 4px;
}

/* Update button styles */
.btn-info {
    padding: 10px 20px;
    font-size: 13px;
    border-radius: 20px;
    background-color: #4b49ac;
    border-color: #4b49ac;
    transition: all 0.2s ease;
}

.btn-info:hover {
    background-color: #3f3e99;
    border-color: #3f3e99;
}

/* Empty state */
.text-center .ti-ticket {
    font-size: 2.5em;
    color: #6c757d;
    margin-bottom: 15px;
}

.text-center .text-muted {
    font-size: 15px;
    margin-bottom: 20px;
}

/* Tab styling */
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 25px;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 12px 25px;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #4b49ac;
    background: none;
    border-bottom: 2px solid #4b49ac;
}

.nav-tabs .nav-link:hover {
    color: #4b49ac;
}

/* Update length menu select focus state */
.dataTables_length select:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
    outline: none;
}

/* Update hover state */
.table tbody tr:hover {
    background-color: #f8f9fa;
    border-bottom: 6px solid #f8f9fa;
}
</style> 