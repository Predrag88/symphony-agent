// AI Alati Browser Extension - Popup Script

document.addEventListener('DOMContentLoaded', function() {
    // Initialize popup
    checkConnectionStatus();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('analyzeBtn').addEventListener('click', analyzePage);
    document.getElementById('optimizeBtn').addEventListener('click', optimizeSEO);
    document.getElementById('generateBtn').addEventListener('click', generateContent);
    document.getElementById('settingsBtn').addEventListener('click', openSettings);
}

function checkConnectionStatus() {
    // Simulate connection check
    const statusEl = document.getElementById('status');
    
    // Try to connect to AI service
    fetch('http://127.0.0.1:8001/api/extension-status')
        .then(response => {
            if (response.ok) {
                statusEl.className = 'status connected';
                statusEl.innerHTML = '🟢 Povezano sa AI servisom';
            } else {
                throw new Error('Service unavailable');
            }
        })
        .catch(() => {
            statusEl.className = 'status disconnected';
            statusEl.innerHTML = '🔴 Nije povezano sa AI servisom';
        });
}

function analyzePage() {
    // Get current tab and analyze page content
    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
        const currentTab = tabs[0];
        
        // Inject content script to analyze page
        chrome.scripting.executeScript({
            target: {tabId: currentTab.id},
            function: extractPageContent
        }, (results) => {
            if (results && results[0]) {
                const pageData = results[0].result;
                sendToAIService('analyze', pageData);
            }
        });
    });
}

function optimizeSEO() {
    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
        const currentTab = tabs[0];
        
        chrome.scripting.executeScript({
            target: {tabId: currentTab.id},
            function: extractSEOData
        }, (results) => {
            if (results && results[0]) {
                const seoData = results[0].result;
                sendToAIService('optimize-seo', seoData);
            }
        });
    });
}

function generateContent() {
    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
        const currentTab = tabs[0];
        
        chrome.scripting.executeScript({
            target: {tabId: currentTab.id},
            function: extractContentContext
        }, (results) => {
            if (results && results[0]) {
                const contextData = results[0].result;
                sendToAIService('generate-content', contextData);
            }
        });
    });
}

function openSettings() {
    chrome.tabs.create({url: 'http://127.0.0.1:8001/extension'});
}

function sendToAIService(action, data) {
    const payload = {
        action: action,
        data: data,
        timestamp: new Date().toISOString()
    };
    
    fetch('http://127.0.0.1:8001/api/extension-process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(result => {
        // Show result notification
        chrome.notifications.create({
            type: 'basic',
            iconUrl: 'icons/icon48.png',
            title: 'AI Alati',
            message: result.message || 'Operacija je uspešno završena!'
        });
        
        // Copy result to clipboard if available
        if (result.content) {
            navigator.clipboard.writeText(result.content);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        chrome.notifications.create({
            type: 'basic',
            iconUrl: 'icons/icon48.png',
            title: 'AI Alati - Greška',
            message: 'Došlo je do greške prilikom obrade zahteva.'
        });
    });
}

// Functions to be injected into page context
function extractPageContent() {
    return {
        title: document.title,
        url: window.location.href,
        content: document.body.innerText.substring(0, 5000),
        headings: Array.from(document.querySelectorAll('h1, h2, h3')).map(h => h.innerText),
        images: Array.from(document.querySelectorAll('img')).map(img => ({
            src: img.src,
            alt: img.alt
        })),
        links: Array.from(document.querySelectorAll('a')).map(a => ({
            href: a.href,
            text: a.innerText
        }))
    };
}

function extractSEOData() {
    const metaTags = {};
    document.querySelectorAll('meta').forEach(meta => {
        const name = meta.getAttribute('name') || meta.getAttribute('property');
        const content = meta.getAttribute('content');
        if (name && content) {
            metaTags[name] = content;
        }
    });
    
    return {
        title: document.title,
        url: window.location.href,
        metaTags: metaTags,
        headings: {
            h1: Array.from(document.querySelectorAll('h1')).map(h => h.innerText),
            h2: Array.from(document.querySelectorAll('h2')).map(h => h.innerText),
            h3: Array.from(document.querySelectorAll('h3')).map(h => h.innerText)
        },
        wordCount: document.body.innerText.split(/\s+/).length
    };
}

function extractContentContext() {
    return {
        title: document.title,
        url: window.location.href,
        domain: window.location.hostname,
        content: document.body.innerText.substring(0, 3000),
        keywords: extractKeywords(),
        language: document.documentElement.lang || 'sr'
    };
}

function extractKeywords() {
    const text = document.body.innerText.toLowerCase();
    const words = text.match(/\b\w{4,}\b/g) || [];
    const frequency = {};
    
    words.forEach(word => {
        frequency[word] = (frequency[word] || 0) + 1;
    });
    
    return Object.entries(frequency)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 10)
        .map(([word]) => word);
}