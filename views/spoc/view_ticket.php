<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Ticket #<?php echo htmlspecialchars($ticket['ticket_number'] ?? 'N/A'); ?></h4>
                            <div>
                                <?php 
                                $statusClass = ($ticket['status'] ?? '') === 'active' ? 'badge-success' : 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $statusClass; ?> mb-2">
                                    <?php echo strtoupper($ticket['status'] ?? 'N/A'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="ticket-info mb-4">
                            <p class="text-muted mb-1">
                                Created on <?php echo date('M d, Y H:i', strtotime($ticket['created_at'] ?? 'now')); ?>
                            </p>
                            <p class="text-muted">
                                By <?php echo htmlspecialchars($ticket['student_name'] ?? 'Unknown Student'); ?>
                            </p>
                        </div>

                        <div class="ticket-content mb-4">
                            <h5><?php echo htmlspecialchars($ticket['subject'] ?? 'No Subject'); ?></h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <?php echo nl2br(htmlspecialchars($ticket['description'] ?? 'No Description')); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Replies Section -->
                        <div class="ticket-replies mb-4">
                            <h5 class="mb-3">Replies</h5>
                            <?php if (empty($replies)): ?>
                                <div class="text-center text-muted p-3">
                                    <i class="ti-comments mb-2" style="font-size: 2em;"></i>
                                    <p>No replies yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($replies as $reply): ?>
                                    <div class="reply <?php echo $reply['user_role'] === 'student' ? 'student-reply' : 'staff-reply'; ?> mb-3">
                                        <div class="reply-header">
                                            <strong><?php echo htmlspecialchars($reply['user_name'] ?? 'Unknown User'); ?></strong>
                                            <span class="badge badge-light"><?php echo ucfirst($reply['user_role'] ?? 'unknown'); ?></span>
                                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></small>
                                        </div>
                                        <div class="reply-content">
                                            <?php echo nl2br(htmlspecialchars($reply['message'] ?? '')); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Reply Form for Active Tickets -->
                        <?php if (($ticket['status'] ?? '') === 'active'): ?>
                            <div class="reply-form">
                                <h5 class="mb-3">Add Reply</h5>
                                <form action="/spoc/add_ticket_reply" method="POST">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <div class="form-group">
                                        <textarea class="form-control" name="message" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Send Reply</button>
                                    <button type="button" class="btn btn-warning" onclick="closeTicket(<?php echo $ticket['id']; ?>)">
                                        Close Ticket
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include ('footer.html'); ?>
</div>

<script>
function closeTicket(ticketId) {
    if (confirm('Are you sure you want to close this ticket?')) {
        $.ajax({
            url: '/spoc/close_ticket',
            type: 'POST',
            data: { ticket_id: ticketId },
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.success) {
                        window.location.href = '/spoc/tickets';
                    } else {
                        alert(result.message || 'Error closing ticket');
                    }
                } catch (e) {
                    alert('Error processing response');
                }
            },
            error: function() {
                alert('Error closing ticket');
            }
        });
    }
}
</script>

<style>
/* Include all the styles from student/view_ticket.php */
.ticket-content {
    border: 1px solid rgba(0,0,0,0.1);
}

.description-content {
    border: 1px solid rgba(0,0,0,0.1);
    white-space: pre-wrap;
}

.conversation-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.reply-wrapper {
    display: flex;
    width: 100%;
}

.reply {
    max-width: 70%;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.student-reply {
    background-color: #e3f2fd;
    margin-right: auto;
    border-left: 4px solid #007bff;
}

.staff-reply {
    background-color: #f8f9fa;
    margin-left: auto;
    border-right: 4px solid #28a745;
}

.reply-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.reply-content {
    white-space: pre-wrap;
}

.badge {
    padding: 5px 10px;
    font-size: 12px;
}

.ticket-replies {
    max-height: 600px;
    overflow-y: auto;
    padding: 20px;
}

/* Scrollbar styling */
.ticket-replies::-webkit-scrollbar {
    width: 8px;
}

.ticket-replies::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.ticket-replies::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.ticket-replies::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style> 