 🎤 Voice Command Text Conversion

A web-based intelligent system that converts voice commands into structured text and machine-readable instructions.  
The project integrates Speech Recognition, Natural Language Processing (NLP), and command parsing to transform spoken instructions into actionable commands.This system demonstrates how voice input can be used to control devices or applications by interpreting human speech and converting it into structured data.

📌 Project Overview:

The Voice Command Text Conversion System allows users to speak commands through a microphone. The system captures the audio, converts it into text using speech recognition, and then processes it to extract meaningful instructions.The interpreted command is displayed in the user interface and also stored in JSON format, making it suitable for integration with IoT devices, automation systems, and smart applications.

✨ Features:

- 🎙 Real-time Voice Recognition
- 🧠 Natural Language Processing for command understanding
- 📊 Structured command interpretation
- 📄 JSON output generation
- 🖥 Interactive web interface
- 📈 Confidence score for command accuracy
- 🔍 Detailed metadata logging
- ⚡ Real-time transcription display

🏗 System Architecture:
User Voice Input
│
▼
Speech Recognition (Browser API)
│
▼
Text Transcription
│
▼
Command Parsing & NLP Processing
│
▼
Structured Command Output
│
├── UI Display
└── JSON File Output

🛠 Technologies Used:

| Technology | Purpose |
|------------|--------|
| HTML5      | Web interface |
| CSS3       | UI styling |
| JavaScript | Application logic |
| Web Speech API | Speech recognition |
| JSON | Structured data storage |
| Browser Local Storage | Saving configuration |

📂 Project Structure:
voice-command-text-conversion/
│
├── voice command spark dashboard.html # Main user interface
├── voice command code.py
└── README.md # Project documentation

⚙️ How It Works:

1. The user clicks the Record button.
2. The system activates the browser speech recognition API.
3. The spoken command is converted into text transcription.
4. The text is processed using NLP parsing rules.
5. The system extracts:
   - Action
   - Target device/object
   - State/value
6. The interpreted command is displayed in the UI.
7. A JSON file is generated with structured command details.

📊 Example Command:

Voice Input
"Open the door"

Parsed Output

| Field   | Value |
|------   |------|
| Action | OPEN |
| Target | DOOR |
| Executable | YES |
| Confidence | 85% |

JSON Output:
{
  "command_id": "CMD_001",
  "timestamp": "2025-12-01T10:30:00",
  "transcription": "Open the door",
  "action": "open",
  "target": "door",
  "executable": true,
  "confidence": 0.85
}

▶️ How to Run the Project:

Download or clone the repository.
git clone https://github.com/your-username/voice-command-text-conversion.git
Open the project folder.
Run the project by opening:
voice command spark dashboard.html
Allow microphone access when prompted.
Click Record and speak a command.

⚠️ Browser Requirements:

Speech recognition works best in:
Google Chrome🎯 
Microsoft Edge
Other browsers may not fully support the Web Speech API.

Applications:

Smart Home Automation
Voice-controlled applications
Accessibility tools
Smart classroom systems
Industrial voice-based control
Virtual assistants

🔮 Future Enhancements:

Multi-language voice recognition
Noise reduction for better accuracy
IoT device integration
Cloud-based command processing
Voice authentication for security
Real-time device control

📚 References:

Web Speech API Documentation
Apache Spark Web UI Documentation
Voice Recognition Research Papers
voice2json Open Source Project

👩‍💻 Author:

Rooba B
B.Tech – Artificial Intelligence and Data Science
M. Kumarasamy College of Engineering, Karur


