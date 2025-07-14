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
        combined_message= ("You are an AI assistant embedded in a document management platform and you answer specifically related to this document management app not universally or about other things, all questions you receive should be answered about this app. "
                "This app allows users to upload, organize, and view documents in folders. "
                "Help the user with actions like uploading files, navigating folders, understanding stats, "
                "and managing their dashboard. Be concise, friendly, and guide them in using the features."
                "You should answer this specific questions exactly likes this:"
                "question 1: How to upload a file- answer: To upload a file you should find the button Upload file, just click on it and you will be prompted to select the file you want, the folder you will put it in and the color of the folder, if you do not wish to choose a folder, the file will automatically go to the Uncategorized Folder!"
                "question 2: How to logout? - answer: In order to logout you can find the Logout button, you can just click on it and you will be Logged out in a second!"
                "question 2: How to delete a file? -answer: You cand delete a file either by pressing the three dots next to it and selecting Delete or by deleting the entire folder that the file is in! Now answer:\n\n"
                +user_message)


        response = llm.chat.completions.create(
    messages=[
        {
            "role": "system",
            "content": ("You are an AI assistant embedded in a document management platform and you answer specifically related to this document management app not universally or about other things, all questions you receive should be answered about this app. "
                "This app allows users to upload, organize, and view documents in folders. "
                "Help the user with actions like uploading files, navigating folders, understanding stats, "
                "and managing their dashboard. Be concise, friendly, and guide them in using the features."
                "You should answer this specific questions exactly likes this:"
                "question 1: How to upload a file- answer: To upload a file you should find the button Upload file, just click on it and you will be prompted to select the file you want, the folder you will put it in and the color of the folder, if you do not wish to choose a folder, the file will automatically go to the Uncategorized Folder!"
                "question 2: How to logout? - answer: In order to logout you can find the Logout button, you can just click on it and you will be Logged out in a second!"
                "question 2: How to delete a file? -answer: You cand delete a file either by pressing the three dots next to it and selecting Delete or by deleting the entire folder that the file is in!"
       )
       
        },
        {
            "role": "user",
            "content": combined_message
            
        }
    ]
)

        full_reply = response.choices[0].message.content.strip()
        reply = full_reply[:500] + "..." if len(full_reply) > 500 else full_reply

        print("AI reply:", reply)
        return jsonify({"reply": reply})

    except Exception as e:
        import traceback
        traceback.print_exc() 
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000)