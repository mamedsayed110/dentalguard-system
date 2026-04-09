/**
 * DentalGuard AI - Frontend Integration with Authentication
 */

// Configuration
const API_BASE_URL = 'http://localhost:8000/api';

// Get auth token from localStorage
function getAuthToken() {
    return localStorage.getItem('auth_token');
}

// Check if user is authenticated
function isAuthenticated() {
    return getAuthToken() !== null;
}

/**
 * Analyze dental X-ray image
 */
async function analyzeDentalXray(imageFile, modelChoice = '1') {
    try {
        // Check authentication
        if (!isAuthenticated()) {
            throw new Error('يجب تسجيل الدخول أولاً');
        }
        
        // Show loading
        showLoading('جاري تحليل الصورة...');
        
        // Convert to base64
        const base64Image = await fileToBase64(imageFile);
        
        // Call API with auth token
        const response = await fetch(`${API_BASE_URL}/ai/analyze`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`
            },
            body: JSON.stringify({
                image: base64Image,
                model: modelChoice
            })
        });
        
        if (response.status === 401) {
            // Unauthorized - redirect to login
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            throw new Error('انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى');
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'فشل التحليل');
        }
        
        hideLoading();
        return data;
        
    } catch (error) {
        hideLoading();
        showError('خطأ في التحليل: ' + error.message);
        throw error;
    }
}

/**
 * Display analysis results
 */
function displayAnalysisResults(results) {
    const resultsContainer = document.getElementById('analysisResults');
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = '';
    
    // Annotated image
    const imageSection = document.createElement('div');
    imageSection.className = 'result-image mb-4';
    imageSection.innerHTML = `
        <h3 class="text-xl font-bold mb-2">صورة الأشعة مع التحديدات</h3>
        <img src="data:image/png;base64,${results.annotated_image}" 
             class="w-full rounded-lg shadow-lg" 
             alt="Annotated X-ray">
    `;
    resultsContainer.appendChild(imageSection);
    
    // Summary
    const summarySection = document.createElement('div');
    summarySection.className = 'result-summary mb-4 p-4 bg-blue-100 rounded-lg';
    summarySection.innerHTML = `
        <h3 class="text-xl font-bold mb-2">ملخص النتائج</h3>
        <p class="text-lg">${results.summary}</p>
        <p class="text-sm text-gray-600 mt-2">
            عدد المشاكل المكتشفة: ${results.detections.length}
        </p>
    `;
    resultsContainer.appendChild(summarySection);
    
    // Detections
    if (results.detections.length > 0) {
        const detectionsSection = document.createElement('div');
        detectionsSection.className = 'result-detections';
        detectionsSection.innerHTML = `
            <h3 class="text-xl font-bold mb-3">التفاصيل</h3>
            <div class="space-y-3">
                ${results.detections.map(det => `
                    <div class="detection-card p-4 bg-white rounded-lg shadow border-r-4 border-red-500">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-lg">${det.class}</h4>
                                <p class="text-sm text-gray-600">
                                    نسبة الثقة: ${(det.confidence * 100).toFixed(1)}%
                                </p>
                            </div>
                            <div class="text-3xl">🦷</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        resultsContainer.appendChild(detectionsSection);
    }
}

/**
 * Send chat message to Gemini
 */
async function sendChatMessage(message) {
    try {
        if (!isAuthenticated()) {
            throw new Error('يجب تسجيل الدخول أولاً');
        }
        
        const response = await fetch(`${API_BASE_URL}/ai/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`
            },
            body: JSON.stringify({ message })
        });
        
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
            throw new Error('انتهت الجلسة');
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'فشل الإرسال');
        }
        
        return data;
        
    } catch (error) {
        showError('خطأ في الاتصال: ' + error.message);
        throw error;
    }
}

/**
 * Display chat message
 */
function displayChatMessage(message, isUser = true) {
    const chatContainer = document.getElementById('chatMessages');
    if (!chatContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isUser ? 'user-message' : 'ai-message'} mb-3`;
    messageDiv.innerHTML = `
        <div class="flex ${isUser ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[80%] p-3 rounded-lg ${
                isUser 
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-200 text-gray-800'
            }">
                <p class="whitespace-pre-wrap">${escapeHtml(message)}</p>
            </div>
        </div>
    `;
    
    chatContainer.appendChild(messageDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Helper functions
function showLoading(message = 'جاري التحميل...') {
    // Implement your loading UI
    console.log(message);
}

function hideLoading() {
    console.log('Loading complete');
}

function showError(message) {
    alert(message);
}

function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Export for use in dashboard
window.DentalAI = {
    analyzeDentalXray,
    displayAnalysisResults,
    sendChatMessage,
    displayChatMessage,
    isAuthenticated
};