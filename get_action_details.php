<?php
include 'config.php';

if(isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];
    
    $query = "SELECT a.*, r.name as request_name, r.quantity as request_quantity, 
              au.companyname, au.companyid 
              FROM action a 
              JOIN requestproduct r ON a.request_id = r.id 
              JOIN autoshop au ON r.companyid = au.companyid 
              WHERE a.request_id = ? 
              ORDER BY a.created_at DESC 
              LIMIT 1";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);
    $action = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if($action) {
        ?>
        <div class="action-details-content">
            <div class="action-header">
                <div class="company-info mb-3">
                    <i class="bi bi-building"></i>
                    <span class="company-name"><?php echo htmlspecialchars($action['companyname']); ?></span>
                </div>
                <h3>Action Details</h3>
                <div class="action-status <?php echo $action['action_type']; ?>">
                    <i class="bi <?php 
                        echo match($action['action_type']) {
                            'confirm' => 'bi-check-circle-fill',
                            'pending' => 'bi-clock-fill',
                            'denied' => 'bi-x-circle-fill'
                        };
                    ?>"></i>
                    <?php echo ucfirst($action['action_type']); ?>
                </div>
            </div>

            <div class="action-body">
                <?php if($action['action_type'] == 'confirm'): ?>
                    <div class="detail-section">
                        <h4><i class="bi bi-box-seam"></i> Product Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Product Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($action['product_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Quantity</span>
                            <span class="detail-value"><?php echo htmlspecialchars($action['quantity']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($action['action_type'] == 'pending'): ?>
                    <div class="detail-section">
                        <h4><i class="bi bi-calendar-event"></i> Estimated Arrival</h4>
                        <?php 
                        $dates = explode(',', $action['estimated_date']);
                        foreach($dates as $date): ?>
                            <div class="detail-row">
                                <span class="detail-value">
                                    <i class="bi bi-calendar2-check"></i>
                                    <?php echo date('F d, Y', strtotime($date)); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if($action['action_type'] == 'denied'): ?>
                    <div class="detail-section">
                        <h4><i class="bi bi-x-octagon"></i> Denial Information</h4>
                        <div class="detail-row">
                            <span class="detail-value"><?php echo htmlspecialchars($action['reason']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($action['image']): ?>
                    <div class="detail-section">
                        <h4><i class="bi bi-image"></i> Product Image</h4>
                        <div class="image-wrapper">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($action['image']); ?>" 
                                 class="action-image" alt="Product Image">
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($action['comments']): ?>
                    <div class="detail-section chat-section">
                        <h4><i class="bi bi-chat-dots-fill"></i> Comments</h4>
                        <div class="chat-content">
                            <div class="chat-container">
                                <div class="chat-history">
                                    <div class="message-wrapper">
                                        <div class="chat-message">
                                            <div class="message-info">
                                                <span class="sender-name">
                                                    <i class="bi bi-building"></i>
                                                    <?php echo htmlspecialchars($action['companyname']); ?>
                                                </span>
                                            </div>
                                            <div class="message-bubble">
                                                <?php echo nl2br(htmlspecialchars($action['comments'])); ?>
                                            </div>
                                            <div class="message-time">
                                                <?php echo date('h:i A · M d, Y', strtotime($action['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="action-footer">
                <div class="timestamp">
                    <i class="bi bi-clock-history"></i>
                    <?php echo date('F d, Y h:i A', strtotime($action['created_at'])); ?>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="p-4 text-center text-muted">No action details available</div>';
    }
} else {
    echo '<p class="text-muted">Select a request to view its details.</p>';
}
?>

<style>
/* Chat Styles */
.chat-section {
    padding: 0 !important;
    overflow: hidden;
}

.chat-section h4 {
    padding: 1rem;
    margin: 0;
    border-bottom: 1px solid #e2e8f0;
}

.chat-content {
    display: flex;
    flex-direction: column;
    height: 300px;
}

.chat-container {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background: #f8f9fa;
}

.chat-history {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message-wrapper {
    display: flex;
    margin-bottom: 1rem;
}

.chat-message {
    max-width: 80%;
    margin-right: auto;
}

.message-info {
    margin-bottom: 0.25rem;
}

.sender-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #4a5568;
}

.sender-name i {
    color: #0d6efd;
}

.message-bubble {
    background: white;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border-top-left-radius: 0.25rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    line-height: 1.5;
    color: #2d3748;
    position: relative;
}

.message-time {
    font-size: 0.75rem;
    color: #718096;
    margin-top: 0.25rem;
    margin-left: 0.5rem;
}

/* Custom Scrollbar */
.chat-container::-webkit-scrollbar {
    width: 6px;
}

.chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-container::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.chat-container::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Adjust detail section for chat */
.detail-section.chat-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    overflow: hidden;
}

.detail-section.chat-section h4 {
    background: #f8f9fa;
    margin: 0;
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
}
</style>

<script>
function submitComment(event, requestId) {
    event.preventDefault();
    const form = event.target;
    const comment = form.comment.value;

    fetch('add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `request_id=${requestId}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the action details to show the new comment
            showActionDetails(requestId);
            // Clear the input
            form.reset();
        } else {
            alert('Error adding comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding comment');
    });
}
</script> 