// توليد معرف فريد للزائر
function generateVisitorId() {
    const components = [
        navigator.userAgent,
        navigator.hardwareConcurrency,
        navigator.deviceMemory,
        screen.width,
        screen.height,
        navigator.language,
        Intl.DateTimeFormat().resolvedOptions().timeZone,
        new Date().getTimezoneOffset()
    ];
    return btoa(components.join('|')).substring(0, 32);
}

// جمع بيانات بصمة الجهاز
function collectDeviceFingerprint() {
    return {
        userAgent: navigator.userAgent,
        platform: navigator.platform,
        hardwareConcurrency: navigator.hardwareConcurrency,
        deviceMemory: navigator.deviceMemory,
        screen: {
            width: screen.width,
            height: screen.height,
            colorDepth: screen.colorDepth,
            pixelDepth: screen.pixelDepth,
            availWidth: screen.availWidth,
            availHeight: screen.availHeight,
            orientation: screen.orientation?.type
        },
        languages: navigator.languages,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        cookieEnabled: navigator.cookieEnabled,
        javaEnabled: navigator.javaEnabled(),
        doNotTrack: navigator.doNotTrack,
        touchSupport: 'ontouchstart' in window
    };
}

// جمع بيانات الشبكة والموقع
async function collectNetworkData() {
    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        return {
            ip: data.ip,
            org: data.org,
            city: data.city,
            region: data.region,
            country: data.country_name,
            countryCode: data.country_code,
            timezone: data.timezone,
            latitude: data.latitude,
            longitude: data.longitude,
            asn: data.asn,
            postal: data.postal
        };
    } catch (error) {
        console.error('Error fetching network data:', error);
        return {};
    }
}

// جمع بيانات الأداء والسلوك
function collectBehaviorData() {
    const timing = window.performance.timing;
    const navigation = window.performance.getEntriesByType('navigation')[0];
    
    return {
        pageLoadTime: timing.loadEventEnd - timing.navigationStart,
        domReadyTime: timing.domComplete - timing.domLoading,
        networkLatency: timing.responseEnd - timing.fetchStart,
        redirectCount: navigation.redirectCount,
        navigationType: navigation.type
    };
}

// جمع بصمة Canvas و WebGL
function collectGraphicsFingerprint() {
    try {
        // Canvas Fingerprinting
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = "14px 'Arial'";
        ctx.fillStyle = '#f60';
        ctx.fillRect(125, 1, 62, 20);
        ctx.fillStyle = '#069';
        ctx.fillText('Advanced Visitor Tracking', 2, 15);
        ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
        ctx.fillText('Advanced Visitor Tracking', 4, 17);
        const canvasData = canvas.toDataURL();
        
        // WebGL Fingerprinting
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        let webglData = {};
        if (gl) {
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            webglData = {
                vendor: gl.getParameter(debugInfo?.UNMASKED_VENDOR_WEBGL || gl.VENDOR),
                renderer: gl.getParameter(debugInfo?.UNMASKED_RENDERER_WEBGL || gl.RENDERER),
                maxTextureSize: gl.getParameter(gl.MAX_TEXTURE_SIZE),
                shaderPrecisionFormat: gl.getShaderPrecisionFormat(gl.FRAGMENT_SHADER, gl.HIGH_FLOAT)
            };
        }
        
        return {
            canvas: canvasData,
            webgl: webglData
        };
    } catch (e) {
        console.error('Graphics fingerprint error:', e);
        return {};
    }
}

// تتبع تفاعلات المستخدم
function setupUserInteractionTracking() {
    const interactions = {
        mouseMovements: [],
        clicks: [],
        scrolls: [],
        keyPresses: []
    };
    
    // تتبع حركة الماوس
    document.addEventListener('mousemove', (e) => {
        interactions.mouseMovements.push({
            x: e.clientX,
            y: e.clientY,
            time: Date.now()
        });
    });
    
    // تتبع النقرات
    document.addEventListener('click', (e) => {
        interactions.clicks.push({
            x: e.clientX,
            y: e.clientY,
            target: e.target.tagName,
            time: Date.now()
        });
    });
    
    // تتبع التمرير
    document.addEventListener('scroll', () => {
        interactions.scrolls.push({
            position: window.scrollY,
            time: Date.now()
        });
    });
    
    // تتبع ضغطات المفاتيح (لا تسجل المحتوى لأسباب أخلاقية)
    document.addEventListener('keydown', (e) => {
        interactions.keyPresses.push({
            key: e.key.length === 1 ? 'CHAR' : e.key,
            time: Date.now()
        });
    });
    
    return interactions;
}

// تتبع مدة الجلسة ونشاط التبويب
function setupSessionTracking() {
    const sessionData = {
        startTime: new Date(),
        lastActive: new Date(),
        isActive: true,
        activeDuration: 0,
        inactiveDuration: 0
    };
    
    let activityCheckInterval;
    
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            sessionData.isActive = false;
            sessionData.inactiveDuration += (new Date() - sessionData.lastActive);
        } else {
            sessionData.isActive = true;
            sessionData.lastActive = new Date();
        }
    });
    
    // تحديث مدة النشاط كل ثانية
    activityCheckInterval = setInterval(() => {
        if (sessionData.isActive) {
            sessionData.activeDuration += 1000;
            sessionData.lastActive = new Date();
        } else {
            sessionData.inactiveDuration += 1000;
        }
    }, 1000);
    
    // إرسال بيانات الجلسة عند المغادرة
    window.addEventListener('beforeunload', () => {
        clearInterval(activityCheckInterval);
        if (sessionData.isActive) {
            sessionData.activeDuration += (new Date() - sessionData.lastActive);
        } else {
            sessionData.inactiveDuration += (new Date() - sessionData.lastActive);
        }
        
        return {
            startTime: sessionData.startTime,
            endTime: new Date(),
            activeDuration: sessionData.activeDuration,
            inactiveDuration: sessionData.inactiveDuration,
            totalDuration: sessionData.activeDuration + sessionData.inactiveDuration
        };
    });
    
    return sessionData;
}

// الدالة الرئيسية لجمع كل البيانات
async function collectAllVisitorData() {
    const visitorId = generateVisitorId();
    const deviceData = collectDeviceFingerprint();
    const networkData = await collectNetworkData();
    const behaviorData = collectBehaviorData();
    const graphicsData = collectGraphicsFingerprint();
    const interactions = setupUserInteractionTracking();
    const sessionData = setupSessionTracking();
    
    return {
        visitorId,
        timestamp: new Date().toISOString(),
        device: deviceData,
        network: networkData,
        behavior: behaviorData,
        graphics: graphicsData,
        interactions,
        session: sessionData,
        page: {
            url: window.location.href,
            referrer: document.referrer,
            title: document.title
        }
    };
}

// إرسال البيانات إلى الخادم
async function sendDataToServer() {
    try {
        const visitorData = await collectAllVisitorData();
        
        // إرسال البيانات باستخدام Beacon API إذا كان متاحًا
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(visitorData)], {type: 'application/json'});
            navigator.sendBeacon('track_visitor.php', blob);
        } else {
            // استخدام fetch كبديل
            fetch('track_visitor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(visitorData)
            });
        }
    } catch (error) {
        console.error('Error collecting visitor data:', error);
    }
}

// بدء التتبع عند تحميل الصفحة
if (document.readyState === 'complete') {
    sendDataToServer();
} else {
    window.addEventListener('load', sendDataToServer);
}

// إرسال بيانات التفاعل كل 30 ثانية
setInterval(() => {
    const interactions = window.interactions || {};
    if (Object.keys(interactions).length > 0) {
        fetch('track_interactions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                visitorId: generateVisitorId(),
                interactions: interactions
            })
        });
        // مسح البيانات بعد الإرسال
        window.interactions = {
            mouseMovements: [],
            clicks: [],
            scrolls: [],
            keyPresses: []
        };
    }
}, 30000);
