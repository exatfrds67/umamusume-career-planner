
function AIAdvisor() {
  const char = MOCK_CHARACTERS[0];
  const [messages, setMessages] = React.useState(AI_MESSAGES_INIT);
  const [input, setInput] = React.useState('');
  const [streaming, setStreaming] = React.useState(false);
  const [streamingText, setStreamingText] = React.useState('');
  const [model, setModel] = React.useState('bedrock');
  const [storageMode] = React.useState('account'); // storage-aware context
  const [tab, setTab] = React.useState('chat'); // 'chat' | 'history'
  const messagesEndRef = React.useRef(null);

  const CONV_HISTORY = [
    { id:1, date:'Apr 7, 2026', summary:'Training priority for Turn 45', model:'bedrock', messageCount:4 },
    { id:2, date:'Apr 5, 2026', summary:'Race strategy for Kanto Okami Cup', model:'ollama', messageCount:6 },
    { id:3, date:'Apr 3, 2026', summary:'Skill build planning with 450 SP', model:'bedrock', messageCount:3 },
  ];

  const SUGGESTED = [
    'What should I train next turn?',
    'Optimize my support deck for the Kanto Okami Cup',
    'Which skills should I prioritize with 450 SP?',
    'Am I on track for URA Finals?',
  ];

  React.useEffect(() => {
    if (messagesEndRef.current) {
      messagesEndRef.current.parentElement.scrollTop = messagesEndRef.current.offsetTop;
    }
  }, [messages, streamingText]);

  const simulateStream = (fullText, onChunk, onDone) => {
    let i = 0;
    const speed = 18; // ms per chunk
    const chunkSize = 4;
    const interval = setInterval(() => {
      i += chunkSize;
      onChunk(fullText.slice(0, Math.min(i, fullText.length)));
      if (i >= fullText.length) { clearInterval(interval); onDone(fullText); }
    }, speed);
  };

  const formatMessage = (text) =>
    text.split('\n').map((line, i) => {
      const html = line.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
      return <div key={i} style={{ marginBottom:line===''?6:2 }} dangerouslySetInnerHTML={{ __html:html||'&nbsp;' }}/>;
    });

  const sendMessage = async (text) => {
    const msg = text || input.trim();
    if (!msg || streaming) return;
    setInput('');
    setMessages(m => [...m, { role:'user', content:msg }]);
    setStreaming(true);
    setStreamingText('');

    const context = `You are an AI advisor for Uma Musume: Pretty Derby (storage_mode: ${storageMode}). Training ${char.name} (Turn ${char.turn}, ${char.stage}). Stats: Speed ${char.stats.speed}, Stamina ${char.stats.stamina}, Power ${char.stats.power}, Guts ${char.stats.guts}, Wit ${char.stats.wit}. SP: ${char.sp}. Scenario: ${char.scenario}. Give concise, game-accurate advice using **bold** for key points. Max 120 words.`;

    try {
      const reply = await window.claude.complete({
        messages: [{ role:'user', content: context + '\n\nUser: ' + msg }]
      });
      simulateStream(
        reply,
        (chunk) => setStreamingText(chunk),
        (full)  => { setMessages(m => [...m, { role:'assistant', content:full }]); setStreaming(false); setStreamingText(''); }
      );
    } catch(e) {
      setMessages(m => [...m, { role:'assistant', content:"Connection issue — please try again." }]);
      setStreaming(false); setStreamingText('');
    }
  };

  const handleKey = (e) => { if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } };

  return (
    <div style={{ display:'flex', gap:20, height:'calc(100vh - 130px)' }}>
      {/* Left sidebar */}
      <div style={{ width:260, flexShrink:0, display:'flex', flexDirection:'column', gap:14 }}>
        {/* Character context */}
        <Card style={{ padding:16 }}>
          <div style={{ fontSize:12, fontWeight:800, color:'#7C6FAB', marginBottom:10 }}>ACTIVE CONTEXT</div>
          <div style={{ display:'flex', gap:8, alignItems:'center', marginBottom:10, padding:'10px', background:'#F9F5FF', borderRadius:10 }}>
            <div style={{ width:36, height:36, borderRadius:10, background:'linear-gradient(135deg,#E879A0,#7C3AED)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16 }}>🐴</div>
            <div>
              <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{char.name}</div>
              <div style={{ fontSize:11, color:'#7C6FAB' }}>Turn {char.turn} · {char.stage}</div>
            </div>
          </div>
          {Object.entries(char.stats).map(([s,v])=>(
            <div key={s} style={{ display:'flex', justifyContent:'space-between', padding:'3px 0', borderBottom:'1px solid #F9F5FF' }}>
              <span style={{ fontSize:12, color:'#7C6FAB' }}>{STAT_ICONS[s]} {STAT_LABELS[s]}</span>
              <span style={{ fontSize:12, fontWeight:800, color:STAT_COLORS[s] }}>{v} <GradeBadge grade={getGrade(v)}/></span>
            </div>
          ))}
          <div style={{ marginTop:10, display:'flex', gap:6 }}>
            <div style={{ flex:1, background:'#FEF3C7', borderRadius:8, padding:'6px', textAlign:'center' }}>
              <div style={{ fontSize:10, color:'#92400E', fontWeight:700 }}>SP</div>
              <div style={{ fontSize:14, fontWeight:900, color:'#F59E0B' }}>{char.sp}</div>
            </div>
            <div style={{ flex:1, background:storageMode==='account'?'#EDE9FE':'#FFFBEB', borderRadius:8, padding:'6px', textAlign:'center' }}>
              <div style={{ fontSize:10, color:'#7C3AED', fontWeight:700 }}>Mode</div>
              <div style={{ fontSize:11, fontWeight:900, color:storageMode==='account'?'#7C3AED':'#F59E0B' }}>{storageMode==='account'?'🟣 Acct':'🟠 Local'}</div>
            </div>
          </div>
        </Card>

        {/* Model selector */}
        <Card style={{ padding:14 }}>
          <div style={{ fontSize:12, fontWeight:800, color:'#7C6FAB', marginBottom:8 }}>AI MODEL · AgentRoutingService</div>
          {[
            { id:'bedrock', label:'AWS Bedrock Claude', sub:'claude-haiku-4-5 · Complex queries', icon:'☁️' },
            { id:'ollama',  label:'Local Ollama',        sub:'llama3.2 · Simple queries',          icon:'💻' },
          ].map(m=>(
            <div key={m.id} onClick={()=>setModel(m.id)} style={{ display:'flex', gap:8, alignItems:'center', padding:'8px 10px', borderRadius:8, cursor:'pointer', background:model===m.id?'#F5F3FF':'transparent', border:model===m.id?'1px solid #C4B5FD':'1px solid transparent', marginBottom:4 }}>
              <span style={{ fontSize:16 }}>{m.icon}</span>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:12, fontWeight:800, color:'#1E1033' }}>{m.label}</div>
                <div style={{ fontSize:10, color:'#7C6FAB' }}>{m.sub}</div>
              </div>
              {model===m.id && <div style={{ width:8, height:8, borderRadius:99, background:'#10B981' }}/>}
            </div>
          ))}
          <div style={{ marginTop:8, fontSize:11, color:'#7C6FAB', padding:'6px 8px', background:'#F9F5FF', borderRadius:8 }}>
            Complex queries → Bedrock · Simple → Ollama (auto-routed)
          </div>
        </Card>

        {/* Suggested prompts */}
        <Card style={{ padding:14, flex:1 }}>
          <div style={{ fontSize:12, fontWeight:800, color:'#7C6FAB', marginBottom:8 }}>SUGGESTED</div>
          {SUGGESTED.map((s,i)=>(
            <button key={i} onClick={()=>sendMessage(s)} disabled={streaming} style={{ display:'block', width:'100%', textAlign:'left', padding:'8px 10px', borderRadius:8, border:'1px solid #EDE9FE', background:'#F9F5FF', cursor:streaming?'not-allowed':'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, color:'#7C3AED', fontWeight:600, marginBottom:6, lineHeight:1.4, opacity:streaming?.5:1 }}>
              {s}
            </button>
          ))}
        </Card>
      </div>

      {/* Chat area */}
      <Card style={{ flex:1, display:'flex', flexDirection:'column', padding:0, overflow:'hidden' }}>
        {/* Tabs */}
        <div style={{ padding:'0 20px', borderBottom:'1px solid #EDE9FE', display:'flex', alignItems:'center', gap:0 }}>
          <div style={{ display:'flex', gap:0, flex:1 }}>
            {['chat','history'].map(t=>(
              <button key={t} onClick={()=>setTab(t)} style={{ padding:'14px 20px', border:'none', borderBottom:`2px solid ${tab===t?'#E879A0':'transparent'}`, background:'transparent', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, color:tab===t?'#E879A0':'#7C6FAB', textTransform:'capitalize' }}>{t==='chat'?'💬 Chat':'🕒 History'}</button>
            ))}
          </div>
          <div style={{ display:'flex', alignItems:'center', gap:8 }}>
            <div style={{ width:7, height:7, borderRadius:99, background: streaming?'#F59E0B':'#10B981' }}/>
            <span style={{ fontSize:11, color: streaming?'#F59E0B':'#10B981', fontWeight:700 }}>{streaming?'Streaming…':'Online'}</span>
            <span style={{ fontSize:11, color:'#7C6FAB' }}>· {model==='bedrock'?'Bedrock':'Ollama'}</span>
          </div>
        </div>

        {tab==='history' && (
          <div style={{ flex:1, overflowY:'auto', padding:'20px' }}>
            <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Conversation History</div>
            {CONV_HISTORY.map(c=>(
              <div key={c.id} style={{ padding:'14px', background:'#F9F5FF', borderRadius:12, marginBottom:10, border:'1px solid #EDE9FE', cursor:'pointer' }}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:6 }}>
                  <div style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>{c.summary}</div>
                  <button onClick={()=>setTab('chat')} style={{ background:'linear-gradient(135deg,#E879A0,#7C3AED)', border:'none', borderRadius:8, padding:'4px 12px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, color:'#fff' }}>Resume</button>
                </div>
                <div style={{ display:'flex', gap:8, fontSize:11, color:'#7C6FAB' }}>
                  <span>{c.date}</span>
                  <span>·</span>
                  <span>{c.messageCount} messages</span>
                  <span>·</span>
                  <span>{c.model==='bedrock'?'☁️ Bedrock':'💻 Ollama'}</span>
                </div>
              </div>
            ))}
          </div>
        )}

        {tab==='chat' && (
          <>
            <div style={{ flex:1, overflowY:'auto', padding:'20px', display:'flex', flexDirection:'column', gap:14 }}>
              {messages.map((msg,i) => (
                <div key={i} style={{ display:'flex', gap:10, alignItems:'flex-start', flexDirection:msg.role==='user'?'row-reverse':'row' }}>
                  <div style={{ width:32, height:32, borderRadius:99, flexShrink:0, background:msg.role==='user'?'linear-gradient(135deg,#E879A0,#7C3AED)':'linear-gradient(135deg,#7C3AED,#1E1033)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:14, fontWeight:800, color:'#fff' }}>
                    {msg.role==='user'?'T':'🤖'}
                  </div>
                  <div style={{ maxWidth:'70%', padding:'12px 16px', borderRadius:msg.role==='user'?'16px 4px 16px 16px':'4px 16px 16px 16px', background:msg.role==='user'?'linear-gradient(135deg,#E879A0,#7C3AED)':'#F5F3FF', color:msg.role==='user'?'#fff':'#1E1033', fontSize:13, lineHeight:1.7, border:msg.role==='assistant'?'1px solid #EDE9FE':'none' }}>
                    {formatMessage(msg.content)}
                  </div>
                </div>
              ))}

              {/* Streaming bubble */}
              {streaming && (
                <div style={{ display:'flex', gap:10, alignItems:'flex-start' }}>
                  <div style={{ width:32, height:32, borderRadius:99, background:'linear-gradient(135deg,#7C3AED,#1E1033)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:14 }}>🤖</div>
                  <div style={{ maxWidth:'70%', padding:'12px 16px', borderRadius:'4px 16px 16px 16px', background:'#F5F3FF', border:'1px solid #EDE9FE', fontSize:13, lineHeight:1.7, color:'#1E1033' }}>
                    {streamingText ? formatMessage(streamingText) : null}
                    <span style={{ display:'inline-block', width:2, height:14, background:'#7C3AED', marginLeft:2, animation:'pulse .6s ease-in-out infinite alternate', verticalAlign:'middle' }}/>
                  </div>
                </div>
              )}
              <div ref={messagesEndRef}/>
            </div>

            <div style={{ padding:'14px 20px', borderTop:'1px solid #EDE9FE', display:'flex', gap:10, flexShrink:0 }}>
              <textarea
                value={input}
                onChange={e=>setInput(e.target.value)}
                onKeyDown={handleKey}
                placeholder="Ask anything about your career run…"
                disabled={streaming}
                style={{ flex:1, padding:'11px 14px', borderRadius:12, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:13, color:'#1E1033', background:'#F9F5FF', outline:'none', resize:'none', height:44, lineHeight:1.5, opacity:streaming?.7:1 }}
              />
              <Btn variant='primary' onClick={()=>sendMessage()} disabled={!input.trim()||streaming} style={{ height:44, paddingTop:0, paddingBottom:0 }}>
                <Icon name='arrowR' size={18} color='#fff'/>
              </Btn>
            </div>
          </>
        )}
      </Card>
    </div>
  );
}

Object.assign(window, { AIAdvisor });
