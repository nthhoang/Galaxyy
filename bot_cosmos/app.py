from flask import Flask, request, jsonify
from flask_cors import CORS
from chatbot import get_response

app = Flask(__name__)
CORS(app)

@app.route("/chat", methods=["POST"])
def chat():
    user_input = request.json.get("message")
    if not user_input:
        return jsonify({"reply": "Bạn chưa nhập câu hỏi."})
    bot_reply = get_response(user_input)
    return jsonify({"reply": bot_reply})

if __name__ == "__main__":
    app.run(port=5001, debug=True)
