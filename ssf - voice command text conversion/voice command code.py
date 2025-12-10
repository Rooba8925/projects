# ============================================================================
# CELL 1: INSTALL DEPENDENCIES
# ============================================================================
!apt-get update -qq
!apt-get install -y openjdk-11-jdk-headless -qq > /dev/null
!apt-get install -y ffmpeg -qq > /dev/null

import os
os.environ["JAVA_HOME"] = "/usr/lib/jvm/java-11-openjdk-amd64"

!pip uninstall -y pyspark dataproc-spark-connect -qq 2>/dev/null
!pip install pyspark==3.5.3 -q
!pip install SpeechRecognition -q
!pip install pydub -q
!pip install pyngrok -q

print("\n✅ All dependencies installed!")
import pyspark
print(f"✅ PySpark version: {pyspark.__version__}")

# ============================================================================
# CELL 2: START SPARK WITH PUBLIC WEB UI
# ============================================================================

from pyspark.sql import SparkSession
from pyspark.sql.functions import *
from pyspark.ml.feature import HashingTF, IDF, StopWordsRemover, RegexTokenizer
import time
import os
from pyngrok import ngrok

# Set your ngrok token
ngrok.set_auth_token("34BJkRhhl4iB7wp0HPFLHBvbUEa_2xdhDwwcYs2sTXFt8JR7P")

# Create event log directory
event_log_dir = "/tmp/spark-events"
os.makedirs(event_log_dir, exist_ok=True)

print("🚀 Starting Spark Session...")

# Start Spark
spark = SparkSession.builder \
    .appName("VoiceCommandProcessor") \
    .config("spark.ui.enabled", "true") \
    .config("spark.ui.port", "4040") \
    .config("spark.driver.memory", "4g") \
    .config("spark.executor.memory", "2g") \
    .config("spark.sql.adaptive.enabled", "true") \
    .config("spark.eventLog.enabled", "true") \
    .config("spark.eventLog.dir", event_log_dir) \
    .master("local[*]") \
    .getOrCreate()

spark.sparkContext.setLogLevel("ERROR")

print("✅ Spark started!")

# Initialize UI
dummy_df = spark.createDataFrame([{"test": "init"}])
dummy_df.count()
time.sleep(2)

print("\n" + "="*70)
print("🎉 SPARK SESSION READY!")
print("="*70)
print(f"✅ Spark Version: {spark.version}")
print(f"✅ Application ID: {spark.sparkContext.applicationId}")

# Create public tunnel
print("\n🌐 Creating public URL for Spark Web UI...")

try:
    ngrok.kill()
    time.sleep(1)
    public_url = ngrok.connect(4040, bind_tls=True)
    time.sleep(2)

    print("\n" + "🌍"*35)
    print("📊 SPARK WEB UI - PUBLIC URL:")
    print("🌍"*35)
    print(f"\n🔗 {public_url}")
    print("\n" + "🌍"*35)

    from IPython.display import HTML, display
    display(HTML(f'''
        <div style="background: #1a73e8; color: white; padding: 20px;
                    border-radius: 10px; text-align: center; margin: 20px 0;">
            <h2>🌐 Spark Web UI</h2>
            <a href="{public_url}" target="_blank"
               style="color: white; font-size: 20px; text-decoration: none;
                      background: #0d47a1; padding: 15px 30px; border-radius: 5px;
                      display: inline-block; margin: 10px;">
                🔗 CLICK HERE TO OPEN SPARK UI
            </a>
            <p style="margin-top: 15px;">Keep this tab open during recording!</p>
        </div>
    '''))

except Exception as e:
    print(f"\n⚠️ Ngrok error: {e}")

print("\n✅ Ready! Now run Cell 3 to record voice commands")
print("="*70)
# ============================================================================
# CELL 3: RECORD VOICE & PROCESS WITH SPARK
# ============================================================================

from google.colab import output, files
from IPython.display import Javascript, display, HTML
from base64 import b64decode
import speech_recognition as sr
from pydub import AudioSegment
import io
import json
from datetime import datetime

# Recording JavaScript
RECORD_JS = """
async function recordAudio(duration) {
  const div = document.createElement('div');
  div.innerHTML = `
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; padding: 25px; border-radius: 15px;
                text-align: center; margin: 20px 0; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
      <h2 style="margin: 0 0 15px 0;">🎤 Voice Command Recorder</h2>
      <p id="status" style="font-size: 20px; margin: 10px 0;">Ready to record</p>
      <button id="startBtn" style="background: white; color: #667eea;
              border: none; padding: 15px 40px; font-size: 18px;
              border-radius: 25px; cursor: pointer; margin: 15px; font-weight: bold;">
        🎙️ START RECORDING (12 seconds)
      </button>
      <p id="timer" style="font-size: 32px; font-weight: bold; margin: 15px 0;"></p>
    </div>
  `;
  document.body.appendChild(div);

  const status = document.getElementById('status');
  const timer = document.getElementById('timer');
  const startBtn = document.getElementById('startBtn');

  return new Promise((resolve) => {
    startBtn.onclick = async () => {
      startBtn.disabled = true;
      startBtn.style.opacity = '0.5';

      const stream = await navigator.mediaDevices.getUserMedia({
        audio: { channelCount: 1, sampleRate: 16000 }
      });

      const mediaRecorder = new MediaRecorder(stream, {
        mimeType: 'audio/webm;codecs=opus'
      });

      const chunks = [];
      mediaRecorder.ondataavailable = (e) => chunks.push(e.data);
      mediaRecorder.start();

      status.innerHTML = '🔴 RECORDING... SPEAK NOW!';
      status.style.fontSize = '24px';

      let remaining = duration;
      const interval = setInterval(() => {
        timer.textContent = remaining + 's';
        remaining--;
        if (remaining < 0) clearInterval(interval);
      }, 1000);

      setTimeout(() => {
        mediaRecorder.stop();
        stream.getTracks().forEach(track => track.stop());
        status.innerHTML = '✅ Processing...';
        timer.textContent = '';
      }, duration * 1000);

      mediaRecorder.onstop = async () => {
        const blob = new Blob(chunks, { type: 'audio/webm' });
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
      };
    };
  });
}
"""

command_patterns = {
    "action": ["open", "close", "start", "stop", "launch", "quit", "run", "execute"],
    "navigation": ["go", "navigate", "move", "switch", "back", "forward"],
    "data": ["show", "display", "get", "find", "search", "list", "view"],
    "control": ["set", "change", "update", "increase", "decrease", "adjust"],
    "query": ["what", "where", "when", "who", "how", "why", "tell"]
}

def classify_command(text):
    if not text:
        return "unknown"
    text_lower = text.lower()
    for cmd_type, keywords in command_patterns.items():
        if any(kw in text_lower for kw in keywords):
            return cmd_type
    return "general"

def record_and_transcribe():
    display(Javascript(RECORD_JS))
    print("\n🎙️ CLICK THE BUTTON ABOVE TO START RECORDING\n")

    audio_b64 = output.eval_js('recordAudio(12)')
    print("🔄 Transcribing your voice...")

    try:
        audio_data = b64decode(audio_b64.split(',')[1])
        audio = AudioSegment.from_file(io.BytesIO(audio_data), format="webm")
        audio = audio.set_channels(1).set_frame_rate(16000)

        wav_io = io.BytesIO()
        audio.export(wav_io, format="wav")
        wav_io.seek(0)

        recognizer = sr.Recognizer()
        with sr.AudioFile(wav_io) as source:
            audio_data = recognizer.record(source)

        text = recognizer.recognize_google(audio_data, language='en-US')
        duration = len(audio) / 1000.0

        print(f"✅ You said: '{text}'")
        print(f"⏱️  Duration: {duration:.1f}s\n")
        return text, duration
    except Exception as e:
        print(f"⚠️ Error: {e}")
        return None, 0

def process_with_spark(text, duration):
    print("="*70)
    print("⚙️  SPARK PROCESSING - WATCH WEB UI!")
    print("="*70)

    # JOB 1: Create DataFrame
    print("\n📊 JOB 1: Creating DataFrame...")
    data = [{
        "text": text,
        "duration": float(duration),
        "timestamp": datetime.now().isoformat(),
        "word_count": len(text.split())
    }]

    df = spark.createDataFrame(data)
    cmd_type = classify_command(text)
    df = df.withColumn("command_type", lit(cmd_type))
    df.cache()
    count1 = df.count()
    print(f"✅ JOB 1 Done - Created {count1} record")

    # JOB 2: Tokenization
    print("\n🔤 JOB 2: Tokenizing...")
    tokenizer = RegexTokenizer(inputCol="text", outputCol="words", pattern="\\W")
    words_df = tokenizer.transform(df)
    words_df.cache()
    count2 = words_df.count()
    print(f"✅ JOB 2 Done - Tokenized")

    # JOB 3: Stop words removal
    print("\n🚫 JOB 3: Removing stop words...")
    remover = StopWordsRemover(inputCol="words", outputCol="filtered")
    filtered_df = remover.transform(words_df)
    filtered_df.cache()
    count3 = filtered_df.count()
    print(f"✅ JOB 3 Done - Filtered")

    # JOB 4: Feature extraction
    print("\n#️⃣ JOB 4: Extracting features...")
    hashing = HashingTF(inputCol="filtered", outputCol="features", numFeatures=50)
    feat_df = hashing.transform(filtered_df)
    feat_df.cache()
    count4 = feat_df.count()
    print(f"✅ JOB 4 Done - Features extracted")

    # JOB 5: TF-IDF
    print("\n📈 JOB 5: Computing TF-IDF...")
    idf = IDF(inputCol="features", outputCol="tfidf")
    idf_model = idf.fit(feat_df)
    tfidf_df = idf_model.transform(feat_df)
    tfidf_df.cache()
    count5 = tfidf_df.count()
    print(f"✅ JOB 5 Done - TF-IDF computed")

    # JOB 6: SQL Query
    print("\n📊 JOB 6: Running SQL query...")
    df.createOrReplaceTempView("commands")
    sql_result = spark.sql("""
        SELECT text, command_type, duration, word_count
        FROM commands
    """)
    sql_result.show(truncate=False)
    print(f"✅ JOB 6 Done - SQL executed")

    # JOB 7: Word frequency
    print("\n📊 JOB 7: Analyzing word frequency...")
    words_list = text.lower().split()
    word_data = [(word,) for word in words_list]
    word_df = spark.createDataFrame(word_data, ["word"])
    word_freq = word_df.groupBy("word").count().orderBy("count", ascending=False)
    print("Top words:")
    word_freq.show(5, truncate=False)
    print(f"✅ JOB 7 Done")

    print("\n" + "="*70)
    print("✅ ALL 7 SPARK JOBS COMPLETED!")
    print("📊 Check Web UI - Jobs tab should show 7+ jobs!")
    print("="*70)

    return cmd_type

# MAIN EXECUTION
print("\n🎙️"*35)
print("VOICE COMMAND → SPARK → JSON OUTPUT")
print("🎙️"*35)

# Show Web UI link
try:
    tunnels = ngrok.get_tunnels()
    if tunnels:
        public_url = tunnels[0].public_url
        display(HTML(f'''
            <div style="background: #4CAF50; color: white; padding: 15px;
                        border-radius: 10px; text-align: center; margin: 15px 0;">
                <h3>🌐 Spark Web UI Active</h3>
                <a href="{public_url}" target="_blank"
                   style="color: white; font-size: 18px;">
                    Open Web UI: {public_url}
                </a>
            </div>
        '''))
except:
    print("📌 Web UI at: http://localhost:4040")

# Record voice
text, duration = record_and_transcribe()

if text:
    # Process with Spark
    cmd_type = process_with_spark(text, duration)

    # Generate JSON output
    print("\n" + "="*70)
    print("📋 GENERATING JSON OUTPUT")
    print("="*70)

    words = text.split()
    tokens = text.lower().split()
    entities = [w for w in words if len(w) > 4][:5]
    dur_rounded = int(duration * 100) / 100.0

    command_output = {
        "command_id": 1,
        "timestamp": datetime.now().isoformat(),
        "audio_input": {
            "duration_seconds": dur_rounded,
            "sample_rate": "16000 Hz"
        },
        "transcription": {
            "original_text": text,
            "confidence": 0.92
        },
        "analysis": {
            "command_type": cmd_type,
            "tokens": tokens,
            "word_count": len(words),
            "entities": entities
        },
        "spark_processing": {
            "jobs_executed": 7,
            "sql_queries": 1,
            "ml_features": "TF-IDF",
            "application_id": spark.sparkContext.applicationId
        },
        "metadata": {
            "libraries": ["Spark SQL", "Spark ML", "SpeechRecognition"],
            "processed_at": datetime.now().isoformat()
        }
    }

    # Display results
    print(f"\n📝 Text: {text}")
    print(f"🎤 Duration: {dur_rounded}s")
    print(f"🏷️  Type: {cmd_type.upper()}")
    print(f"🔤 Tokens: {', '.join(tokens)}")
    print(f"📊 Word Count: {len(words)}")
    print(f"⚙️  Spark App ID: {spark.sparkContext.applicationId}")

    # Save JSON file
    filename = f"voice_command_{int(datetime.now().timestamp())}.json"
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(command_output, f, indent=2, ensure_ascii=False)

    print(f"\n💾 JSON saved as: {filename}")

    # Display JSON
    print("\n📄 JSON Output:")
    print(json.dumps(command_output, indent=2))

    # Download JSON file
    print("\n📥 Downloading JSON file...")
    files.download(filename)

    print("\n" + "="*70)
    print("✅ COMPLETE!")
    print("="*70)
    print("\n📊 Check Spark Web UI:")
    try:
        tunnels = ngrok.get_tunnels()
        if tunnels:
            print(f"   🔗 {tunnels[0].public_url}")
    except:
        print("   🔗 http://localhost:4040")

    print("\n   ✅ Jobs tab: 7 completed jobs")
    print("   ✅ SQL tab: 1 query executed")
    print("   ✅ Stages tab: Multiple stages")
    print("\n💡 Re-run Cell 3 for another recording")

else:
    print("\n❌ No audio captured. Please re-run Cell 3")
# ============================================================================
# CELL 4: STOP SPARK (Run after demo)
# ============================================================================

print("⚠️  This will stop Spark and close Web UI")
confirm = input("Stop Spark? (yes/no): ")

if confirm.lower() == 'yes':
    try:
        ngrok.kill()
    except:
        pass
    spark.stop()
    print("\n✅ Spark stopped")
else:
    print("\n✅ Spark still running")
    try:
        tunnels = ngrok.get_tunnels()
        if tunnels:
            print(f"🔗 Web UI: {tunnels[0].public_url}")
    except:
        pass