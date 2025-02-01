<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <!-- Header Section -->
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="font-weight-bold mb-0">Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?></h4>
                    </div>
                    <div>
                        <?php if ($ticket['status'] === 'active'): ?>
                            <button onclick="closeTicket(<?php echo $ticket['id']; ?>)" class="btn btn-warning mr-2">
                                <i class="ti-close"></i> Close Ticket
                            </button>
                        <?php endif; ?>
                        <a href="/student/tickets" class="btn btn-secondary">
                            <i class="ti-arrow-left"></i> Back to Tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Details -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge <?php echo $ticket['status'] === 'active' ? 'badge-success' : 'badge-secondary'; ?> mb-2">
                                    <?php echo strtoupper($ticket['status']); ?>
                                </span>
                                <p class="text-muted mb-0">Created on <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></p>
                            </div>
                            <?php if ($ticket['status'] === 'closed'): ?>
                                <div class="text-muted">
                                    <small>Closed on <?php echo date('M d, Y H:i', strtotime($ticket['closed_at'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Subject and Description -->
                        <div class="ticket-content bg-light p-4 rounded">
                            <div class="subject-section mb-4">
                                <label class="text-muted mb-2">Subject:</label>
                                <h5 class="font-weight-bold"><?php echo htmlspecialchars($ticket['subject']); ?></h5>
                            </div>
                            <div class="description-section">
                                <label class="text-muted mb-2">Description:</label>
                                <div class="description-content bg-white p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replies Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Conversation History</h5>
                        <div class="ticket-replies">
                            <?php if (empty($replies)): ?>
                                <div class="text-center text-muted p-3">
                                    <i class="ti-comments" style="font-size: 2em;"></i>
                                    <p class="mt-2">No replies yet</p>
                                </div>
                            <?php else: ?>
                                <div class="conversation-container">
                                    <?php foreach ($replies as $reply): ?>
                                        <?php 
                                        $isStudent = $reply['user_role'] === 'student';
                                        $alignClass = $isStudent ? 'student-reply' : 'staff-reply';
                                        $roleClass = $isStudent ? 'text-primary' : 'text-success';
                                        ?>
                                        <div class="reply-wrapper <?php echo $isStudent ? 'justify-content-start' : 'justify-content-end'; ?>">
                                            <div class="reply <?php echo $alignClass; ?>">
                                                <div class="reply-header">
                                                    <strong><?php echo htmlspecialchars($reply['user_name']); ?></strong>
                                                    <span class="badge badge-light <?php echo $roleClass; ?>">
                                                        <?php echo ucfirst($reply['user_role']); ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <div class="reply-content">
                                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($ticket['status'] === 'active'): ?>
                            <div class="reply-form mt-4">
                                <form id="replyForm" method="POST" action="/student/add_ticket_reply">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <div class="form-group">
                                        <label>Your Reply</label>
                                        <textarea class="form-control" name="message" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Send Reply</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-4">
                                <i class="ti-info-circle mr-2"></i>
                                This ticket is closed. To reopen this issue, please create a new ticket and reference ticket #<?php echo $ticket['ticket_number']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<script>
function closeTicket(ticketId) {
    if (confirm('Are you sure you want to close this ticket?')) {
        $.ajax({
            url: '/student/close_ticket',
            method: 'POST',
            data: { ticket_id: ticketId },
            success: function(response) {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error closing ticket');
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