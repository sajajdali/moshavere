<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>در حال به‌روزرسانی</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;500;700&display=swap" rel="stylesheet"> --}}

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Vazirmatn', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #141e30, #243b55);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .container {
            text-align: center;
            padding: 40px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            max-width: 500px;
            width: 90%;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: rotate 6s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        h1 {
            font-size: 26px;
            margin-bottom: 15px;
        }

        p {
            font-size: 15px;
            opacity: 0.85;
            margin-bottom: 25px;
            line-height: 1.8;
        }

        .timer {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .progress {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            transition: width 1s linear;
        }

        .progress-bar {
            position: relative;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            overflow: hidden;
            transition: width 1s linear;
        }

        /* موج */
        .progress-bar::before {
            content: "";
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;

            background: repeating-linear-gradient(45deg,
                    rgba(255, 255, 255, 0.15) 0px,
                    rgba(255, 255, 255, 0.15) 10px,
                    rgba(255, 255, 255, 0.05) 10px,
                    rgba(255, 255, 255, 0.05) 20px);

            animation: waveMove 2s linear infinite;
        }

        /* پالس نور */
        .progress-bar::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;

            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, 0.6),
                    transparent);

            animation: shine 2.5s ease-in-out infinite;
        }

        @keyframes waveMove {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(50%);
            }
        }

        @keyframes shine {
            0% {
                left: -100%;
            }

            50% {
                left: 100%;
            }

            100% {
                left: 100%;
            }
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            opacity: 0.6;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="icon">⚙️</div>

        <h1>سایت در حال به‌روزرسانی است</h1>

        <p>
            در حال بهبود سیستم هستیم. لطفاً تا پایان زمان زیر منتظر بمانید 🙏
        </p>

        <div class="timer" id="timer">02:00:00</div>

        <div class="progress">
            <div class="progress-bar" id="progress"></div>
        </div>

        <div class="footer">
            به‌روزرسانی در حال انجام...
        </div>
    </div>

    <script>
        const duration = 2 * 60 * 60 * 1000;
        const startTime = new Date("2026-04-03T15:00:00").getTime();

        // زمان پایان
        const endTime = startTime + duration;

        const timerEl = document.getElementById('timer');
        const progressEl = document.getElementById('progress');

        function update() {
            const now = new Date().getTime();
            const remaining = endTime - now;

            if (remaining <= 0) {
                timerEl.innerHTML = "00:00:00";
                progressEl.style.width = "100%";
                return;
            }

            // محاسبه زمان
            const hours = Math.floor(remaining / (1000 * 60 * 60));
            const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

            timerEl.innerHTML =
                String(hours).padStart(2, '0') + ":" +
                String(minutes).padStart(2, '0') + ":" +
                String(seconds).padStart(2, '0');

            // درصد پیشرفت
            const progress = ((now - startTime) / duration) * 100;
            progressEl.style.width = progress + "%";
        }

        setInterval(update, 1000);
        update();
    </script>

</body>

</html>
