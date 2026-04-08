"""
DentalGuard AI Server - FIXED VERSION
Flask API for YOLO dental analysis + Gemini medical advisor
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
from ultralytics import YOLO
import google.generativeai as genai
from PIL import Image
import io
import os
import base64
import cv2
import numpy as np
from datetime import datetime

app = Flask(__name__)
CORS(app)

# ========================================
# Configuration
# ========================================

# YOLO Models
MODEL_1_PATH = "models/dental_v10_best.pt"
MODEL_2_PATH = "models/teeth_detection_best.pt"

# Gemini API Key
GEMINI_API_KEY = "AIzaSyBEA8WUwox-7E3fR5XEGoLxYOHQCQGtLWU"  # ← Replace with your key

# ========================================
# Initialize
# ========================================

print("="*50)
print("🦷 DentalGuard AI Server - Initializing...")
print("="*50)

# Load Models
print(f"[1/3] Loading Model 1: {MODEL_1_PATH}")
model_1 = None
model_2 = None

try:
    if os.path.exists(MODEL_1_PATH):
        model_1 = YOLO(MODEL_1_PATH)
        print("✅ Model 1 loaded successfully!")
    else:
        print(f"⚠️  Model 1 not found: {MODEL_1_PATH}")
except Exception as e:
    print(f"❌ Error loading Model 1: {e}")

try:
    if os.path.exists(MODEL_2_PATH):
        model_2 = YOLO(MODEL_2_PATH)
        print("✅ Model 2 loaded successfully!")
    else:
        print(f"⚠️  Model 2 not found: {MODEL_2_PATH}")
        print("    Training may still be in progress...")
except Exception as e:
    print(f"❌ Error loading Model 2: {e}")

# Configure Gemini
print("[3/3] Initializing Gemini AI...")
try:
    if GEMINI_API_KEY and GEMINI_API_KEY != "AIzaSyBEA8WUwox-7E3fR5XEGoLxYOHQCQGtLWU":
        genai.configure(api_key=GEMINI_API_KEY)
        # Use updated model name
        gemini_model = genai.GenerativeModel('gemini-1.5-flash')
        print("✅ Gemini configured successfully!")
    else:
        gemini_model = None
        print("⚠️  Gemini API key not configured")
except Exception as e:
    gemini_model = None
    print(f"⚠️  Gemini initialization warning: {e}")

print("="*50)
print("🚀 Server ready!")
print("="*50)

# ========================================
# Helper Functions
# ========================================

def get_condition_color(class_name):
    """Return color for different dental conditions"""
    colors = {
        'caries': '#EF4444',
        'cavity': '#EF4444',
        'decay': '#EF4444',
        'calculus': '#F59E0B',
        'tartar': '#F59E0B',
        'gingivitis': '#F59E0B',
        'periapical': '#DC2626',
        'impacted': '#7C3AED',
        'fracture': '#DC2626',
        'missing': '#6B7280',
    }
    
    class_lower = class_name.lower()
    for key, color in colors.items():
        if key in class_lower:
            return color
    
    return '#06B6D4'

def generate_report(detections):
    """Generate Arabic medical report"""
    if not detections:
        return {
            'summary': 'الأسنان تبدو بحالة جيدة! لم يتم اكتشاف أي مشاكل واضحة.',
            'advice': 'حافظ على نظافة أسنانك بالفرشاة والخيط يومياً.'
        }
    
    num_issues = len(detections)
    
    severe = [d for d in detections if 'caries' in d['class'].lower() or 'periapical' in d['class'].lower()]
    moderate = [d for d in detections if 'calculus' in d['class'].lower() or 'gingivitis' in d['class'].lower()]
    
    if severe:
        summary = f'تم اكتشاف {num_issues} مشكلة تحتاج اهتمام! منها {len(severe)} حالة خطيرة.'
        advice = '⚠️ يُنصح بزيارة طبيب الأسنان فوراً للعلاج.'
    elif moderate:
        summary = f'تم اكتشاف {num_issues} مشكلة بسيطة إلى متوسطة.'
        advice = 'يُنصح بتحسين روتين تنظيف الأسنان وزيارة الطبيب قريباً.'
    else:
        summary = f'تم اكتشاف {num_issues} حالة تحتاج مراقبة.'
        advice = 'حافظ على نظافة أسنانك واستشر الطبيب في الزيارة القادمة.'
    
    return {'summary': summary, 'advice': advice}

# ========================================
# API Endpoints
# ========================================

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'online',
        'model_1': 'loaded' if model_1 else 'not_found',
        'model_2': 'loaded' if model_2 else 'not_found',
        'gemini': 'configured' if gemini_model else 'not_configured',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/predict', methods=['POST'])
def predict():
    """
    AI Dental Analysis Endpoint
    Accepts: multipart/form-data with 'image' file
    Returns: JSON with detections and annotated image
    """
    
    print("📥 Received prediction request")
    
    try:
        # Check if image in request
        if 'image' not in request.files:
            return jsonify({
                'success': False,
                'error': 'No image provided'
            }), 400
        
        # Get image
        file = request.files['image']
        img = Image.open(file.stream)
        
        print(f"✅ Image loaded: {img.size}")
        
        # Choose model
        model = model_1 if model_1 else model_2
        
        if not model:
            return jsonify({
                'success': False,
                'error': 'No model available'
            }), 500
        
        print("🤖 Running inference...")
        
        # Run prediction
        results = model.predict(img, conf=0.25, verbose=False)
        
        # Parse results
        detections = []
        
        # Convert image for annotation
        img_array = np.array(img)
        if len(img_array.shape) == 2:  # Grayscale
            img_array = cv2.cvtColor(img_array, cv2.COLOR_GRAY2RGB)
        elif img_array.shape[2] == 4:  # RGBA
            img_array = cv2.cvtColor(img_array, cv2.COLOR_RGBA2RGB)
        
        for r in results:
            boxes = r.boxes
            for box in boxes:
                # Get coordinates
                x1, y1, x2, y2 = box.xyxy[0].cpu().numpy()
                
                # Get class and confidence
                cls = int(box.cls[0])
                conf = float(box.conf[0])
                class_name = model.names[cls]
                
                # Draw on image
                color_hex = get_condition_color(class_name)
                color_bgr = tuple(int(color_hex[i:i+2], 16) for i in (5, 3, 1))  # Hex to BGR
                
                cv2.rectangle(img_array, 
                             (int(x1), int(y1)), 
                             (int(x2), int(y2)), 
                             color_bgr, 3)
                
                cv2.putText(img_array, 
                           f'{class_name} {conf:.2f}',
                           (int(x1), int(y1)-10),
                           cv2.FONT_HERSHEY_SIMPLEX,
                           0.9, color_bgr, 2)
                
                # Create detection object
                detection = {
                    'label': f'{class_name} ({conf:.0%})',
                    'class': class_name,
                    'confidence': conf,
                    'box': [int(x1), int(y1), int(x2-x1), int(y2-y1)],
                    'color': color_hex
                }
                detections.append(detection)
        
        print(f"✅ Found {len(detections)} detections")
        
        # Generate report
        report = generate_report(detections)
        
        # Convert annotated image to base64
        _, buffer = cv2.imencode('.jpg', cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR))
        img_base64 = base64.b64encode(buffer).decode('utf-8')
        
        return jsonify({
            'success': True,
            'detections': detections,
            'count': len(detections),
            'report_summary': report['summary'],
            'doctor_advice': report['advice'],
            'annotated_image': img_base64,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        print(f"❌ Error during prediction: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/chat', methods=['POST'])
def chat():
    """Gemini Medical Chatbot Endpoint"""
    
    try:
        data = request.get_json()
        user_message = data.get('message', '')
        
        if not user_message:
            return jsonify({
                'success': False,
                'error': 'No message provided'
            }), 400
        
        print(f"💬 Processing chat: {user_message[:50]}...")
        
        if not gemini_model:
            return jsonify({
                'success': False,
                'error': 'Gemini not configured'
            }), 500
        
        # Create medical context prompt
        prompt = f"""أنت مستشار طبي متخصص في صحة الأسنان.

القواعد:
- أجب باللغة العربية بشكل واضح ومختصر
- قدم نصائح عامة فقط
- شجع المستخدم على زيارة الطبيب للحالات الخطيرة
- كن لطيفاً ومطمئناً

سؤال المستخدم: {user_message}

الإجابة:"""
        
        # Call Gemini
        response = gemini_model.generate_content(prompt)
        
        print("✅ Chat response generated")
        
        return jsonify({
            'success': True,
            'response': response.text,
            'timestamp': datetime.now().isoformat()
        })
        
    except Exception as e:
        print(f"❌ Error during chat: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/models/status', methods=['GET'])
def models_status():
    """Get models status"""
    return jsonify({
        'model_1': {
            'loaded': model_1 is not None,
            'path': MODEL_1_PATH,
            'exists': os.path.exists(MODEL_1_PATH)
        },
        'model_2': {
            'loaded': model_2 is not None,
            'path': MODEL_2_PATH,
            'exists': os.path.exists(MODEL_2_PATH)
        },
        'gemini': {
            'configured': gemini_model is not None,
            'api_key_set': GEMINI_API_KEY != "AIzaSyBEA8WUwox-7E3fR5XEGoLxYOHQCQGtLWU"
        }
    })

# ========================================
# Run Server
# ========================================

if __name__ == '__main__':
    print("\n🌐 Starting Flask server...")
    print("📍 Server URL: http://localhost:5000")
    print("💡 Endpoints:")
    print("   GET  /health         - Health check")
    print("   POST /predict        - Analyze X-ray")
    print("   POST /chat           - Medical chatbot")
    print("   GET  /models/status  - Models status")
    print("⚠️  Press Ctrl+C to stop")
    
    app.run(host='0.0.0.0', port=5000, debug=True)