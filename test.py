import openai

openai.api_key = "fw_3ZinnJwZRwTMSSazsjJLvqri"
openai.base_url = "https://api.fireworks.ai/inference/v1"

try:
    res = openai.chat.completions.create(
        model="accounts/fireworks/models/llama-v3p1-8b-instruct",  
        messages=[{"role": "user", "content": "Say hello!"}]
    )
    print(res.choices[0].message.content)
except Exception as e:
    print(" ERROR:", e)
