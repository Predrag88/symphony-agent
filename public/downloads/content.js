// AI Alati Browser Extension - Content Script

(function() {
    'use strict';
    
    // Initialize content script
    let aiToolsPanel = null;
    let isInitialized = false;
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    function initialize() {
        if (isInitialized) return;
        isInitialized = true;
        
        createFloatingPanel();
        setupKeyboardShortcuts();
        observePageChanges();
    }
    
    function createFloatingPanel() {
        // Create floating AI tools panel
        aiToolsPanel = document.createElement('div');
        aiToolsPanel.id = 'ai-tools-panel';
        aiToolsPanel.innerHTML = `
            <div class="ai-tools-header">
                <span class="ai-tools-logo">🤖</span>
                <span class="ai-tools-title">AI Alati</span>
                <button class="ai-tools-close" id="ai-tools-close">×</button>
            </div>
            <div class="ai-tools-content">
                <button class="ai-tool-btn" data-action="analyze">
                    <span class="ai-tool-icon">🔍</span>
                    <span>Analiziraj</span>
                </button>
                <button class="ai-tool-btn" data-action="optimize">
                    <span class="ai-tool-icon">⚡</span>
                    <span>Optimizuj</span>
                </button>
                <button class="ai-tool-btn" data-action="generate">
                    <span class="ai-tool-icon">✨</span>
                    <span>Generiši</span>
                </button>
                <button class="ai-tool-btn" data-action="extract">
                    <span class="ai-tool-icon">📋</span>
                    <span>Izvuci Tekst</span>
                </button>
            </div>
            <div class="ai-tools-status" id="ai-tools-status">
                Spreman za rad
            </div>
        `;
        
        // Add panel to page
        document.body.appendChild(aiToolsPanel);
        
        // Setup event listeners
        setupPanelEvents();
        
        // Initially hide panel
        aiToolsPanel.style.display = 'none';
    }
    
    function setupPanelEvents() {
        // Close button
        document.getElementById('ai-tools-close').addEventListener('click', () => {
            aiToolsPanel.style.display = 'none';
        });
        
        // Tool buttons
        document.querySelectorAll('.ai-tool-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.getAttribute('data-action');
                executeAction(action);
            });
        });
        
        // Make panel draggable
        makeDraggable(aiToolsPanel);
    }
    
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+Shift+A to toggle panel
            if (e.ctrlKey && e.shiftKey && e.key === 'A') {
                e.preventDefault();
                togglePanel();
            }
            
            // Ctrl+Shift+Q for quick analyze
            if (e.ctrlKey && e.shiftKey && e.key === 'Q') {
                e.preventDefault();
                executeAction('analyze');
            }
        });
    }
    
    function togglePanel() {
        if (aiToolsPanel.style.display === 'none') {
            aiToolsPanel.style.display = 'block';
            positionPanel();
        } else {
            aiToolsPanel.style.display = 'none';
        }
    }
    
    function positionPanel() {
        // Position panel in top-right corner
        aiToolsPanel.style.position = 'fixed';
        aiToolsPanel.style.top = '20px';
        aiToolsPanel.style.right = '20px';
        aiToolsPanel.style.zIndex = '10000';
    }
    
    function executeAction(action) {
        updateStatus('Obrađujem...');
        
        switch (action) {
            case 'analyze':
                analyzeCurrentPage();
                break;
            case 'optimize':
                optimizePageSEO();
                break;
            case 'generate':
                generateContentSuggestions();
                break;
            case 'extract':
                extractPageText();
                break;
        }
    }
    
    function analyzeCurrentPage() {
        const pageData = {
            title: document.title,
            url: window.location.href,
            content: document.body.innerText.substring(0, 3000),
            wordCount: document.body.innerText.split(/\s+/).length,
            headings: getHeadings(),
            images: getImages(),
            links: getLinks()
        };
        
        sendToAIService('analyze', pageData)
            .then(result => {
                showResult('Analiza završena', result.summary);
            })
            .catch(error => {
                updateStatus('Greška pri analizi');
            });
    }
    
    function optimizePageSEO() {
        const seoData = {
            title: document.title,
            metaDescription: getMetaDescription(),
            headings: getHeadings(),
            content: document.body.innerText.substring(0, 5000),
            images: getImages(),
            url: window.location.href
        };
        
        sendToAIService('optimize-seo', seoData)
            .then(result => {
                showResult('SEO optimizacija', result.suggestions);
            })
            .catch(error => {
                updateStatus('Greška pri SEO optimizaciji');
            });
    }
    
    function generateContentSuggestions() {
        const contextData = {
            title: document.title,
            content: document.body.innerText.substring(0, 2000),
            keywords: extractKeywords(),
            domain: window.location.hostname
        };
        
        sendToAIService('generate-content', contextData)
            .then(result => {
                showResult('Sadržaj generisan', result.content);
                copyToClipboard(result.content);
            })
            .catch(error => {
                updateStatus('Greška pri generisanju sadržaja');
            });
    }
    
    function extractPageText() {
        const extractedText = document.body.innerText;
        copyToClipboard(extractedText);
        showResult('Tekst izvučen', 'Tekst je kopiran u clipboard');
    }
    
    function sendToAIService(action, data) {
        return fetch('http://127.0.0.1:8001/api/extension-process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                data: data,
                timestamp: new Date().toISOString()
            })
        }).then(response => response.json());
    }
    
    function updateStatus(message) {
        const statusEl = document.getElementById('ai-tools-status');
        if (statusEl) {
            statusEl.textContent = message;
        }
    }
    
    function showResult(title, content) {
        updateStatus('Završeno!');
        
        // Create result notification
        const notification = document.createElement('div');
        notification.className = 'ai-tools-notification';
        notification.innerHTML = `
            <div class="ai-notification-header">
                <strong>${title}</strong>
                <button class="ai-notification-close">×</button>
            </div>
            <div class="ai-notification-content">${content}</div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
        
        // Close button
        notification.querySelector('.ai-notification-close').addEventListener('click', () => {
            notification.parentNode.removeChild(notification);
        });
    }
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('Text copied to clipboard');
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }
    
    // Helper functions
    function getHeadings() {
        return {
            h1: Array.from(document.querySelectorAll('h1')).map(h => h.innerText),
            h2: Array.from(document.querySelectorAll('h2')).map(h => h.innerText),
            h3: Array.from(document.querySelectorAll('h3')).map(h => h.innerText)
        };
    }
    
    function getImages() {
        return Array.from(document.querySelectorAll('img')).map(img => ({
            src: img.src,
            alt: img.alt || '',
            title: img.title || ''
        }));
    }
    
    function getLinks() {
        return Array.from(document.querySelectorAll('a')).map(a => ({
            href: a.href,
            text: a.innerText.trim()
        })).filter(link => link.text.length > 0);
    }
    
    function getMetaDescription() {
        const metaDesc = document.querySelector('meta[name="description"]');
        return metaDesc ? metaDesc.getAttribute('content') : '';
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
    
    function makeDraggable(element) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        const header = element.querySelector('.ai-tools-header');
        
        header.onmousedown = dragMouseDown;
        
        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            document.onmousemove = elementDrag;
        }
        
        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            element.style.top = (element.offsetTop - pos2) + "px";
            element.style.left = (element.offsetLeft - pos1) + "px";
        }
        
        function closeDragElement() {
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }
    
    function observePageChanges() {
        // Observe DOM changes for SPA navigation
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Page content changed, update status
                    updateStatus('Stranica ažurirana');
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
})();