<!DOCTYPE html>
<html>
<head>
    <title>Scan QR Code for Attendance</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
</head>
<body>
    <h2>Scan QR Code for Attendance</h2>
    <video id="qr-video" width="300" height="200"></video>
    <canvas id="qr-canvas" hidden></canvas>
    <p id="result"></p>

    <script>
        const video = document.getElementById("qr-video");
        const canvas = document.getElementById("qr-canvas");
        const context = canvas.getContext("2d");

        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function (stream) {
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                video.play();
                requestAnimationFrame(tick);
            });

        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    document.getElementById("result").innerText = "Scanned: " + code.data;
                    markAttendance(code.data);
                }
            }
            requestAnimationFrame(tick);
        }

        function markAttendance(ticketData) {
            fetch("mark_attendance.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "ticket_data=" + encodeURIComponent(ticketData)
            })
            .then(response => response.text())
            .then(data => alert(data))
            .catch(error => console.error("Error:", error));
        }
    </script>
</body>
</html>
