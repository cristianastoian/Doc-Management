from flask import Flask, request, jsonify
from flask_cors import CORS
from fireworks import LLM
import os
import logging

app = Flask(__name__)
CORS(app, resources=r'/*', allow_headers=['Content-Type', 'Authorization', 'Origin', 'X-Requested-With', 'Accept'], allow_methods=['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'], expose_headers=['Content-Type', 'Authorization'])

API_KEY = os.getenv("FIREWORKS_API_KEY") 
MODEL_NAME = "accounts/fireworks/models/llama-v3p1-8b-instruct"

try:
    llm = LLM(model=MODEL_NAME, deployment_type="serverless", api_key=API_KEY)
    logging.info(" LLM initialized successfully")
except Exception as e:
    logging.error(f" Failed to initialize LLM: {e}")
    raise

@app.before_request
def before_request():
    if request.method == 'OPTIONS':
        return jsonify({'Access-Control-Allow-Origin': '*','Access-Control-Allow-Methods': 'GET,PUT,POST,DELETE,OPTIONS', 'Access-Control-Allow-Headers': 'Content-Type,Authorization,Origin,X-Requested-With,Accept', 'Content-Type': 'application/json'})

@app.after_request
def after_request(response):
    if request.method == 'OPTIONS':
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Methods'] = 'GET,PUT,POST,DELETE,OPTIONS'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type,Authorization,Origin,X-Requested-With,Accept'
        response.headers['Access-Control-Expose-Headers'] = 'Content-Type,Authorization'
        return response
    return response

@app.route("/generate", methods=["POST"])
def generate():
    try:
        data = request.get_json(force=True)
        if not data or "message" not in data:
            return jsonify({"error": "Missing 'message' field"}), 400

        user_message = data["message"]
        print("User message:", user_message)

        response = llm.chat.completions.create(
    messages=[
        {
            "role": "system",
            "content": ""
        },
        {
            "role": "user",
            "content": user_message
        }
    ]
)

        full_reply = response.choices[0].message.content.strip()
        reply = full_reply[:100] + "..." if len(full_reply) > 100 else full_reply

        print("AI reply:", reply)
        return jsonify({"reply": reply})

    except Exception as e:
        import traceback
        traceback.print_exc() 
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000)