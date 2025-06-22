from flask import Flask, request, jsonify
import openai
from getToken import receiveToken


openai.api_key = receiveToken("FW")
openai.base_url = "https://api.fireworks.ai/inference/v1"

app = Flask(__name__)

@app.route("/generate", methods=["POST"])
def generate():
    data = request.get_json()
    user_message = data.get("text", "")

    messages = [
        {"role": "system", "content": "You are a helpful assistant for a document management web application."},
        {"role": "user", "content": user_message}
    ]

    try:
        response = openai.chat.completions.create(
            model="accounts/fireworks/models/llama-v3p1-405b-instruct",
            messages=messages,
            max_tokens=512,
            temperature=0.6,
            top_p=1,
            frequency_penalty=0,
            presence_penalty=0
        )
        answer = response.choices[0].message.content.strip()
        return jsonify({"reply": answer})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(port=5000)