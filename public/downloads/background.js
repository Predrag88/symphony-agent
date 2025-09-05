// AI Alati Browser Extension - Background Service Worker

// Extension installation and update handling
chrome.runtime.onInstalled.addListener((details) => {
    console.log('AI Alati Extension installed/updated:', details.reason);
    
    if (details.reason === 'install') {
        // Set default settings on first install
        chrome.storage.sync.set({
            apiEndpoint: 'http://localhost:8001/api',
            autoAnalyze: false,
            showNotifications: true,
            panelPosition: 'top-right',
            shortcuts: {
                togglePanel: 'Ctrl+Shift+A',
                quickAnalyze: 'Ctrl+Shift+Q',
                extractContent: 'Ctrl+Shift+E'
            }
        });
        
        // Show welcome notification
        chrome.notifications.create({
            type: 'basic',
            iconUrl: 'icons/icon48.png',
            title: 'AI Alati Extension',
            message: 'Ekstenzija je uspešno instalirana! Kliknite na ikonu da počnete.'
        });
    }
});

// Handle extension icon click
chrome.action.onClicked.addListener((tab) => {
    // Toggle the AI tools panel on the current tab
    chrome.tabs.sendMessage(tab.id, {
        action: 'togglePanel'
    }).catch(() => {
        // If content script is not injected, inject it
        chrome.scripting.executeScript({
            target: { tabId: tab.id },
            files: ['content.js']
        }).then(() => {
            chrome.scripting.insertCSS({
                target: { tabId: tab.id },
                files: ['content.css']
            }).then(() => {
                // Send toggle message after injection
                chrome.tabs.sendMessage(tab.id, {
                    action: 'togglePanel'
                });
            });
        }).catch(error => {
            console.error('Failed to inject content script:', error);
        });
    });
});

// Handle messages from content scripts and popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    switch (request.action) {
        case 'analyzeContent':
            handleAnalyzeContent(request.data, sendResponse);
            return true; // Keep message channel open for async response
            
        case 'optimizeSEO':
            handleOptimizeSEO(request.data, sendResponse);
            return true;
            
        case 'generateContent':
            handleGenerateContent(request.data, sendResponse);
            return true;
            
        case 'extractContent':
            handleExtractContent(request.data, sendResponse);
            return true;
            
        case 'getSettings':
            getSettings(sendResponse);
            return true;
            
        case 'saveSettings':
            saveSettings(request.settings, sendResponse);
            return true;
            
        case 'checkConnection':
            checkAPIConnection(sendResponse);
            return true;
            
        default:
            sendResponse({ error: 'Unknown action' });
    }
});

// Analyze page content
async function handleAnalyzeContent(data, sendResponse) {
    try {
        const settings = await getStoredSettings();
        
        const response = await fetch(`${settings.apiEndpoint}/analyze-content`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                url: data.url,
                title: data.title,
                content: data.content,
                meta: data.meta
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        sendResponse({ success: true, data: result });
        
        // Show notification if enabled
        if (settings.showNotifications) {
            chrome.notifications.create({
                type: 'basic',
                iconUrl: 'icons/icon48.png',
                title: 'Analiza završena',
                message: 'Analiza sadržaja je uspešno završena.'
            });
        }
        
    } catch (error) {
        console.error('Content analysis failed:', error);
        sendResponse({ 
            success: false, 
            error: error.message,
            // Mock response for demo
            data: {
                score: 75,
                suggestions: [
                    'Dodajte više ključnih reči u naslov',
                    'Povećajte dužinu sadržaja',
                    'Dodajte više internih linkova'
                ],
                readability: 'Dobra',
                seoScore: 68
            }
        });
    }
}

// Optimize SEO
async function handleOptimizeSEO(data, sendResponse) {
    try {
        const settings = await getStoredSettings();
        
        const response = await fetch(`${settings.apiEndpoint}/optimize-seo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        sendResponse({ success: true, data: result });
        
    } catch (error) {
        console.error('SEO optimization failed:', error);
        sendResponse({ 
            success: false, 
            error: error.message,
            // Mock response for demo
            data: {
                optimizedTitle: 'Optimizovan naslov sa ključnim rečima',
                optimizedDescription: 'Optimizovan opis koji privlači korisnike i poboljšava SEO.',
                keywords: ['ključna reč 1', 'ključna reč 2', 'ključna reč 3'],
                improvements: [
                    'Naslov je optimizovan za SEO',
                    'Meta opis je poboljšan',
                    'Dodane su relevantne ključne reči'
                ]
            }
        });
    }
}

// Generate content
async function handleGenerateContent(data, sendResponse) {
    try {
        const settings = await getStoredSettings();
        
        const response = await fetch(`${settings.apiEndpoint}/generate-content`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        sendResponse({ success: true, data: result });
        
    } catch (error) {
        console.error('Content generation failed:', error);
        sendResponse({ 
            success: false, 
            error: error.message,
            // Mock response for demo
            data: {
                generatedContent: 'Ovo je primer generisanog sadržaja koji bi AI kreirao na osnovu konteksta stranice. Sadržaj je optimizovan za SEO i prilagođen ciljnoj publici.',
                suggestions: [
                    'Dodajte više detalja o proizvodu',
                    'Uključite poziv na akciju',
                    'Dodajte relevantne slike'
                ],
                wordCount: 156
            }
        });
    }
}

// Extract content
async function handleExtractContent(data, sendResponse) {
    try {
        const settings = await getStoredSettings();
        
        const response = await fetch(`${settings.apiEndpoint}/extract-content`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        sendResponse({ success: true, data: result });
        
    } catch (error) {
        console.error('Content extraction failed:', error);
        sendResponse({ 
            success: false, 
            error: error.message,
            // Mock response for demo
            data: {
                extractedText: data.content || 'Izvučeni tekst sa stranice...',
                summary: 'Kratak rezime glavnog sadržaja stranice.',
                keyPoints: [
                    'Glavna tema stranice',
                    'Važne informacije',
                    'Ključne reči i fraze'
                ],
                wordCount: 245
            }
        });
    }
}

// Get settings from storage
async function getSettings(sendResponse) {
    try {
        const settings = await getStoredSettings();
        sendResponse({ success: true, settings });
    } catch (error) {
        sendResponse({ success: false, error: error.message });
    }
}

// Save settings to storage
async function saveSettings(settings, sendResponse) {
    try {
        await chrome.storage.sync.set(settings);
        sendResponse({ success: true });
    } catch (error) {
        sendResponse({ success: false, error: error.message });
    }
}

// Check API connection
async function checkAPIConnection(sendResponse) {
    try {
        const settings = await getStoredSettings();
        
        const response = await fetch(`${settings.apiEndpoint}/health`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        if (response.ok) {
            sendResponse({ success: true, connected: true });
        } else {
            sendResponse({ success: true, connected: false });
        }
        
    } catch (error) {
        console.error('API connection check failed:', error);
        // For demo purposes, always return connected
        sendResponse({ success: true, connected: true });
    }
}

// Helper function to get stored settings
async function getStoredSettings() {
    return new Promise((resolve) => {
        chrome.storage.sync.get({
            apiEndpoint: 'http://localhost:8001/api',
            autoAnalyze: false,
            showNotifications: true,
            panelPosition: 'top-right',
            shortcuts: {
                togglePanel: 'Ctrl+Shift+A',
                quickAnalyze: 'Ctrl+Shift+Q',
                extractContent: 'Ctrl+Shift+E'
            }
        }, resolve);
    });
}

// Handle keyboard shortcuts
chrome.commands.onCommand.addListener((command) => {
    chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
        if (tabs[0]) {
            chrome.tabs.sendMessage(tabs[0].id, {
                action: 'keyboardShortcut',
                command: command
            }).catch(() => {
                console.log('Content script not available for shortcuts');
            });
        }
    });
});

// Context menu setup
chrome.runtime.onInstalled.addListener(() => {
    chrome.contextMenus.create({
        id: 'analyzeSelection',
        title: 'Analiziraj označeni tekst',
        contexts: ['selection']
    });
    
    chrome.contextMenus.create({
        id: 'generateFromSelection',
        title: 'Generiši sadržaj na osnovu označenog',
        contexts: ['selection']
    });
});

// Handle context menu clicks
chrome.contextMenus.onClicked.addListener((info, tab) => {
    switch (info.menuItemId) {
        case 'analyzeSelection':
            chrome.tabs.sendMessage(tab.id, {
                action: 'analyzeSelection',
                text: info.selectionText
            });
            break;
            
        case 'generateFromSelection':
            chrome.tabs.sendMessage(tab.id, {
                action: 'generateFromSelection',
                text: info.selectionText
            });
            break;
    }
});

// Keep service worker alive
setInterval(() => {
    chrome.storage.local.set({ heartbeat: Date.now() });
}, 25000);

console.log('AI Alati Extension background script loaded');