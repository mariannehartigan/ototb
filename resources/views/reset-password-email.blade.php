<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Password Reset</title>
  <style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        padding: 40px;
    }
    .container {
        max-width: 600px;
        margin: auto;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
        color: #111827;
    }
    .text {
        color: #374151;
        font-size: 15px;
        margin-bottom: 24px;
        line-height: 1.5;
        text-align: center;
    }
    .btn-wrapper {
        text-align: center;
    }
    .btn {
        display: inline-block;
        background-color: #6366F1;
        color: white;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        margin-top: 10px;
    }
  </style>
</head>
<body>
  <table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <table class="container" width="600" cellpadding="0" cellspacing="0">
        <tr>
          <td class="title">Custom Reset Password Email</td>
        </tr>
        <tr>
          <td class="text"><br><br>
            Hi {{ $name }},<br><br><br>
            We received a request to reset your password. <br><br>
          </td>
        </tr>
        <tr>
          <td class="btn-wrapper">
            <a href="{{ $resetUrl }}" class="btn">Reset Password</a><br>
          </td>
        </tr>
          <td class="text" style="padding-top: 40px;">
            Thank you for choosing us!
            <br><br>
            <img src="https://picsum.photos/400/30" />
          </td>
      </table>
    </td>
  </tr>
</table>
</body>
</html>