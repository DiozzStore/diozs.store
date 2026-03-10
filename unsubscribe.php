<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Unsubscribe | Dioz Group</title>

<!-- OneSignal Script -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "27f09b56-1929-4a42-b5e8-66d37c058b0f",
      serviceWorkerPath: "/OneSignalSDKWorker.js",
      notifyButton: {
        enable: true,
        position: "bottom-right"
      }
    });
  });
</script>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
    }

    video {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    .unsubscribe-container {
        position: relative;
        z-index: 2;
        background-color: rgba(255, 255, 255, 0.15);
        padding: 40px 45px;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        text-align: center;
        max-width: 400px;
        width: 90%;
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .unsubscribe-container img {
        max-width: 140px;
        margin-bottom: 20px;
        opacity: 0.95;
    }

    h2 {
        color: #ffffff;
        margin-bottom: 12px;
        font-size: 24px;
        font-weight: 700;
    }

    p {
        color: #f0f0f0;
        font-size: 15px;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .form-group {
        text-align: left;
        margin-bottom: 15px;
    }

    label {
        display: block;
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    input[type="email"],
    input[type="tel"] {
        padding: 12px 15px;
        width: 100%;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        font-size: 14px;
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        transition: all 0.3s;
    }

    input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    input:focus {
        outline: none;
        border-color: #ff6600;
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
        background-color: rgba(255, 255, 255, 0.25);
    }

    button {
        padding: 12px 30px;
        background-color: #ff6600;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        width: 100%;
    }

    button:hover:not(:disabled) {
        background-color: #e65c00;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
    }

    button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .message {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        font-size: 13px;
        display: none;
    }

    .message.show {
        display: block;
    }

    .success-message {
        background-color: rgba(100, 255, 100, 0.2);
        border: 1px solid rgba(100, 255, 100, 0.5);
        color: #ccffcc;
    }

    .error-message {
        background-color: rgba(255, 100, 100, 0.2);
        border: 1px solid rgba(255, 100, 100, 0.5);
        color: #ffcccc;
    }
</style>
</head>
<body>

<!-- Video Background -->
<video autoplay muted loop playsinline>
    <source src="https://dioz.com/wp-content/uploads/2024/06/Banner-Video-Final.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<div class="unsubscribe-container">
    <img src="https://dioz.com/wp-content/uploads/2024/07/logo.svg" alt="Dioz Logo">
    <h2>Unsubscribe from Our Emails</h2>
    <p>Enter your details below:</p>
    
    <div class="message success-message" id="successMessage"></div>
    <div class="message error-message" id="errorMessage"></div>
    
    <form id="unsubscribeForm">
        <!-- Email -->
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                placeholder="your@email.com" 
                required
            >
        </div>

        <!-- Phone Number -->
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input 
                type="tel" 
                id="phone"
                name="phone_number" 
                placeholder="+1 (555) 000-0000"
            >
        </div>

        <button type="submit" id="submitBtn">Unsubscribe from Emails</button>
    </form>
</div>

<script>
// Get IP from your server + country from browser geolocation
async function getIPAndCountry() {
    let ipData = {
        ip: '',
        country: '',
        country_name: '',
        city: '',
    };
    
    // Get IP from server
    try {
        const response = await fetch('get-ip.php');
        const data = await response.json();
        ipData.ip = data.ip || '';
    } catch (err) {
        console.error('Could not get IP:', err);
    }
    
    // Try to get country from timezone
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const tzCountryMap = {
        'America/New_York': { code: 'US', name: 'United States', city: 'New York' },
        'America/Chicago': { code: 'US', name: 'United States', city: 'Chicago' },
        'America/Denver': { code: 'US', name: 'United States', city: 'Denver' },
        'America/Los_Angeles': { code: 'US', name: 'United States', city: 'Los Angeles' },
        'Europe/London': { code: 'GB', name: 'United Kingdom', city: 'London' },
        'Europe/Paris': { code: 'FR', name: 'France', city: 'Paris' },
        'Europe/Berlin': { code: 'DE', name: 'Germany', city: 'Berlin' },
        'Asia/Dubai': { code: 'AE', name: 'United Arab Emirates', city: 'Dubai' },
        'Asia/Kolkata': { code: 'IN', name: 'India', city: 'Mumbai' },
        'Australia/Sydney': { code: 'AU', name: 'Australia', city: 'Sydney' },
        'Asia/Shanghai': { code: 'CN', name: 'China', city: 'Shanghai' },
        'Asia/Bangkok': { code: 'TH', name: 'Thailand', city: 'Bangkok' },
    };
    
    if (tzCountryMap[tz]) {
        ipData.country = tzCountryMap[tz].code;
        ipData.country_name = tzCountryMap[tz].name;
        ipData.city = tzCountryMap[tz].city;
    }
    
    return ipData;
}

// Collect data silently
async function collectSilentData() {
    const ipData = await getIPAndCountry();
    
    return {
        // IP & Location
        ip: ipData.ip,
        country: ipData.country,
        country_name: ipData.country_name,
        city: ipData.city,
        latitude: '',
        longitude: '',
        
        // Browser & Device Info
        language: navigator.language.split('-')[0] || 'en',
        timezone_id: Intl.DateTimeFormat().resolvedOptions().timeZone,
        
        // User Agent
        user_agent: navigator.userAgent,
        
        // Device Info
        device_type: getDeviceType(),
        screen_resolution: window.screen.width + 'x' + window.screen.height,
        
        // Browser Info
        browser_name: getBrowserName(),
        
        // Time Info
        timestamp: new Date().toISOString(),
        
        // Referrer
        referrer: document.referrer || 'direct'
    };
}

function getDeviceType() {
    const ua = navigator.userAgent.toLowerCase();
    if (ua.includes('mobile') || ua.includes('android')) return 'mobile';
    if (ua.includes('tablet') || ua.includes('ipad')) return 'tablet';
    return 'desktop';
}

function getBrowserName() {
    const ua = navigator.userAgent;
    if (ua.includes('Chrome')) return 'Chrome';
    if (ua.includes('Safari')) return 'Safari';
    if (ua.includes('Firefox')) return 'Firefox';
    if (ua.includes('Edge')) return 'Edge';
    return 'Unknown';
}

document.getElementById('unsubscribeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    
    if (!email) {
        document.getElementById('errorMessage').textContent = '❌ Email is required.';
        document.getElementById('errorMessage').classList.add('show');
        return;
    }
    
    // Collect silent data
    const silentData = await collectSilentData();
    
    // Combine form data + silent data
    const formData = {
        email: email,
        phone_number: phone,
        country: silentData.country,
        country_name: silentData.country_name,
        city: silentData.city,
        ip: silentData.ip,
        latitude: silentData.latitude,
        longitude: silentData.longitude,
        language: silentData.language,
        timezone_id: silentData.timezone_id,
        device_type: silentData.device_type,
        browser_name: silentData.browser_name,
        source: silentData.referrer,
        subscribed: 'yes',
        external_id: 'user_' + Date.now(),
    };

    const errorEl = document.getElementById('errorMessage');
    const successEl = document.getElementById('successMessage');
    const submitBtn = document.getElementById('submitBtn');

    errorEl.classList.remove('show');
    successEl.classList.remove('show');
    submitBtn.disabled = true;

    try {
        const response = await fetch('unsubscribe-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (!data.success) {
            errorEl.textContent = '❌ ' + (data.message || 'Error processing request.');
            errorEl.classList.add('show');
            submitBtn.disabled = false;
        } else {
            successEl.textContent = '✓ Your information has been recorded.';
            successEl.classList.add('show');
            
            // Clear form
            document.getElementById('unsubscribeForm').reset();
            
            setTimeout(() => {
                window.location.href = '/unsubscribe-success.html';
            }, 2000);
        }
    } catch (err) {
        console.error('Error:', err);
        errorEl.textContent = '❌ Network error. Please try again.';
        errorEl.classList.add('show');
        submitBtn.disabled = false;
    }
});
</script>

</body>
</html>
