<?php
$razorpayOrderId = $_SESSION['razorpay_order_id'];
$amount = $_SESSION['amount'];
$studentId = $_SESSION['student_id'];
$courseId = $_SESSION['course_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <form id="razorpay-form" action="/student/razorpay_callback" method="POST">
        <input type="hidden" name="razorpay_order_id" value="<?php echo $razorpayOrderId; ?>">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
    </form>

    <script>
        var options = {
            "key": "rzp_test_ILKXehI3hPXJdo",
            "amount": "<?php echo $amount * 100; ?>", // Amount in paise
            "currency": "INR",
            "name": "Your Company Name",
            "description": "Course Enrollment",
            "order_id": "<?php echo $razorpayOrderId; ?>",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('razorpay-form').submit();
            },
            "prefill": {
                "name": "<?php echo $_SESSION['student_name']; ?>",
                "email": "<?php echo $_SESSION['email']; ?>"
            },
            "theme": {
                "color": "#F37254"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.open();
    </script>
</body>
</html>