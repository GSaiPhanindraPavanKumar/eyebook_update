<!-- Include Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- Payment Form -->
<form id="paymentForm">
    <input type="hidden" id="course_id" name="course_id" value="<?php echo $course['id']; ?>">
    <button type="button" id="payButton" class="btn btn-primary">Subscribe</button>
</form>

<script>
document.getElementById('payButton').onclick = function(e) {
    var courseId = document.getElementById('course_id').value;
    var options = {
        "key": "YOUR_RAZORPAY_KEY", // Enter the Key ID generated from the Dashboard
        "amount": "<?php echo $course['price'] * 100; ?>", // Amount is in currency subunits. Default currency is INR.
        "currency": "INR",
        "name": "Course Subscription",
        "description": "Subscription for " + "<?php echo $course['name']; ?>",
        "handler": function (response){
            // Handle payment success
            fetch('payment_success.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    payment_id: response.razorpay_payment_id,
                    course_id: courseId
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      alert('Payment successful! You are now subscribed to the course.');
                      window.location.reload();
                  } else {
                      alert('Payment failed. Please try again.');
                  }
              });
        },
        "prefill": {
            "name": "<?php echo $student['name']; ?>",
            "email": "<?php echo $student['email']; ?>"
        },
        "theme": {
            "color": "#3399cc"
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
    e.preventDefault();
}
</script>