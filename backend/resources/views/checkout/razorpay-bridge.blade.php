<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Completing payment…</title>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<style>
  body { margin:0; font-family:-apple-system,system-ui,sans-serif; background:#fff; display:flex; align-items:center; justify-content:center; height:100vh; color:#6B7280; font-size:14px; }
</style>
</head>
<body>
<div id="status">Opening secure payment…</div>
<script>
  (function () {
    var params = new URLSearchParams(window.location.search);
    var send = function (payload) {
      if (window.ReactNativeWebView) {
        window.ReactNativeWebView.postMessage(JSON.stringify(payload));
      }
    };

    var key = params.get('key');
    var orderId = params.get('order_id');
    var amount = params.get('amount');
    var currency = params.get('currency') || 'INR';

    if (!key || !orderId || !amount) {
      document.getElementById('status').textContent = 'Missing payment details.';
      send({ status: 'failed', message: 'Missing payment details.' });
      return;
    }

    var rzp = new Razorpay({
      key: key,
      order_id: orderId,
      amount: amount,
      currency: currency,
      name: 'Hardware Centers',
      description: 'Order payment',
      theme: { color: '#F97316' },
      handler: function (response) {
        send({
          status: 'success',
          razorpay_order_id: response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature,
        });
      },
      modal: {
        ondismiss: function () {
          send({ status: 'cancelled' });
        },
      },
    });
    rzp.on('payment.failed', function (resp) {
      send({ status: 'failed', message: resp.error && resp.error.description ? resp.error.description : 'Payment failed.' });
    });
    rzp.open();
  })();
</script>
</body>
</html>
