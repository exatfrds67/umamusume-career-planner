
function Onboarding({ onComplete }) {
  const [step, setStep] = React.useState('welcome'); // welcome | storage | profile | preferences | tour
  const [tutorialEnabled, setTutorialEnabled] = React.useState(true);
  const [storageMode, setStorageMode] = React.useState(null); // 'local' | 'account'
  const [username, setUsername] = React.useState('');
  const [avatar, setAvatar] = React.useState(null);
  const [theme, setTheme] = React.useState('system');
  const [language, setLanguage] = React.useState('en');
  const [tourStep, setTourStep] = React.useState(0);

  const AVATARS = [
    { id:1, icon:'🐴', label:'Mejiro Ardan' },
    { id:2, icon:'🦄', label:'Kitasan Black' },
    { id:3, icon:'🌸', label:'Tokai Teio' },
    { id:4, icon:'⚡', label:'Silence Suzuka' },
    { id:5, icon:'🌟', label:'Special Week' },
    { id:6, icon:'🔥', label:'Gold Ship' },
  ];

  const TOUR_STEPS = [
    { target:'dashboard',     icon:'🏠', title:'Dashboard',       desc:'Your career at a glance — stats, goals, upcoming races, and your latest AI tip.' },
    { target:'training',      icon:'⚡', title:'Training',         desc:'Pick a training each turn. The AI recommends the best option based on your goals and energy.' },
    { target:'races',         icon:'🏆', title:'Races',            desc:'Plan race strategy, check readiness, and record results with fan and SP rewards.' },
    { target:'ai-advisor',    icon:'🤖', title:'AI Advisor',        desc:'Ask anything about your run. Powered by AWS Bedrock Claude or local Ollama.' },
    { target:'achievements',  icon:'🎖️', title:'Achievements',     desc:'Track milestones and earn SP rewards for reaching stats, winning races, and growing your fanbase.' },
  ];

  const STEPS = ['welcome', 'storage', 'profile', 'preferences', tutorialEnabled ? 'tour' : null].filter(Boolean);
  const stepIdx = STEPS.indexOf(step);
  const totalSteps = STEPS.length;

  const StepProgress = () => (
    <div style={{ display:'flex', gap:6, justifyContent:'center', marginBottom:28 }}>
      {STEPS.map((s,i) => (
        <div key={s} style={{ width: i === stepIdx ? 24 : 8, height:8, borderRadius:99, background: i < stepIdx ? '#10B981' : i === stepIdx ? 'linear-gradient(90deg,#E879A0,#7C3AED)' : 'rgba(255,255,255,0.2)', transition:'all .3s' }}/>
      ))}
    </div>
  );

  const handleFinish = () => {
    localStorage.setItem('uma_onboarded', '1');
    localStorage.setItem('uma_storage_mode', storageMode || 'local');
    onComplete();
  };

  // ── Welcome ──────────────────────────────────────────────────────────────
  if (step === 'welcome') return (
    <div style={{ minHeight:'100vh', background:'linear-gradient(135deg,#0A0620 0%,#150D35 50%,#1E0A3C 100%)', display:'flex', alignItems:'center', justifyContent:'center', padding:24, position:'relative', overflow:'hidden' }}>
      {/* Decorative orbs */}
      <div style={{ position:'absolute', top:-120, right:-80, width:400, height:400, borderRadius:'50%', background:'radial-gradient(circle,rgba(232,121,160,0.15),transparent 70%)', pointerEvents:'none' }}/>
      <div style={{ position:'absolute', bottom:-80, left:-60, width:300, height:300, borderRadius:'50%', background:'radial-gradient(circle,rgba(124,58,237,0.2),transparent 70%)', pointerEvents:'none' }}/>

      <div style={{ maxWidth:560, width:'100%', textAlign:'center', position:'relative' }}>
        {/* Logo */}
        <div style={{ width:80, height:80, borderRadius:24, background:'linear-gradient(135deg,#E879A0,#7C3AED)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:40, margin:'0 auto 24px', boxShadow:'0 12px 40px rgba(232,121,160,0.4)' }}>🏇</div>

        <div style={{ fontSize:13, fontWeight:800, color:'rgba(232,121,160,0.8)', letterSpacing:2, textTransform:'uppercase', marginBottom:10 }}>Welcome to</div>
        <h1 style={{ fontSize:36, fontWeight:900, margin:'0 0 6px', background:'linear-gradient(135deg,#F9A8D4,#C4B5FD)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent', lineHeight:1.1 }}>Uma Musume<br/>Career Planner</h1>
        <div style={{ fontSize:14, color:'rgba(255,255,255,0.5)', marginBottom:36, lineHeight:1.7 }}>
          Your comprehensive tool for planning, tracking, and optimizing<br/>training runs in Uma Musume: Pretty Derby
        </div>

        {/* Value props */}
        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:12, marginBottom:36 }}>
          {[
            { icon:'📊', label:'Stat Tracking',    desc:'Track all 5 stats with grade indicators' },
            { icon:'🤖', label:'AI Advisory',       desc:'Claude-powered training recommendations' },
            { icon:'🏆', label:'Race Strategy',     desc:'Readiness scores and win probability' },
          ].map(item=>(
            <div key={item.label} style={{ background:'rgba(255,255,255,0.07)', borderRadius:14, padding:'16px 12px', border:'1px solid rgba(255,255,255,0.08)' }}>
              <div style={{ fontSize:28, marginBottom:8 }}>{item.icon}</div>
              <div style={{ fontSize:12, fontWeight:800, color:'rgba(255,255,255,0.9)', marginBottom:4 }}>{item.label}</div>
              <div style={{ fontSize:11, color:'rgba(255,255,255,0.4)', lineHeight:1.4 }}>{item.desc}</div>
            </div>
          ))}
        </div>

        {/* Tutorial toggle */}
        <div style={{ display:'flex', justifyContent:'center', alignItems:'center', gap:12, marginBottom:28, padding:'12px 20px', background:'rgba(255,255,255,0.06)', borderRadius:14, border:'1px solid rgba(255,255,255,0.1)' }}>
          <div onClick={()=>setTutorialEnabled(!tutorialEnabled)} style={{ width:44, height:24, borderRadius:99, background:tutorialEnabled?'linear-gradient(135deg,#E879A0,#7C3AED)':'rgba(255,255,255,0.2)', cursor:'pointer', position:'relative', transition:'background .2s', flexShrink:0 }}>
            <div style={{ position:'absolute', top:2, left:tutorialEnabled?22:2, width:20, height:20, borderRadius:99, background:'#fff', transition:'left .2s', boxShadow:'0 1px 4px rgba(0,0,0,0.3)' }}/>
          </div>
          <div style={{ textAlign:'left' }}>
            <div style={{ fontSize:13, fontWeight:700, color:'rgba(255,255,255,0.9)' }}>Enable interactive tutorial</div>
            <div style={{ fontSize:11, color:'rgba(255,255,255,0.4)' }}>Guided tour of key features after setup</div>
          </div>
        </div>

        <Btn variant='primary' size='lg' style={{ width:'100%', justifyContent:'center', fontSize:16 }} onClick={()=>setStep('storage')}>
          Get Started →
        </Btn>
        <div style={{ fontSize:12, color:'rgba(255,255,255,0.3)', marginTop:14 }}>v2.4.2 · Global English Server</div>
      </div>
    </div>
  );

  // ── Storage Mode Selection ────────────────────────────────────────────────
  if (step === 'storage') return (
    <div style={{ minHeight:'100vh', background:'linear-gradient(135deg,#0A0620,#150D35)', display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
      <div style={{ maxWidth:620, width:'100%' }}>
        <StepProgress />
        <div style={{ textAlign:'center', marginBottom:32 }}>
          <div style={{ fontSize:24, fontWeight:900, color:'#fff', marginBottom:6 }}>Choose Your Storage Mode</div>
          <div style={{ fontSize:14, color:'rgba(255,255,255,0.5)' }}>How would you like to save your career run data?</div>
        </div>

        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginBottom:24 }}>
          {[
            {
              id:'local', icon:'🟠', title:'Local Mode', color:'#F59E0B',
              pros:['No login required','Works offline','Instant start','Data stays on your device'],
              cons:['Single device only','Risk if browser cache cleared'],
              best:'Quick testing · Anonymous usage',
            },
            {
              id:'account', icon:'🟣', title:'Account Mode', color:'#7C3AED',
              pros:['Cross-device sync','Secure cloud backup','Access anywhere','Account features'],
              cons:['Requires internet','Login required'],
              best:'Long-term tracking · Multi-device',
            },
          ].map(mode=>{
            const sel = storageMode === mode.id;
            return (
              <div key={mode.id} onClick={()=>setStorageMode(mode.id)} style={{ borderRadius:20, border:`2px solid ${sel?mode.color:'rgba(255,255,255,0.1)'}`, background:sel?`linear-gradient(135deg,${mode.color}18,${mode.color}08)`:'rgba(255,255,255,0.04)', padding:24, cursor:'pointer', transition:'all .2s', position:'relative' }}>
                {sel && <div style={{ position:'absolute', top:14, right:14, width:24, height:24, borderRadius:99, background:mode.color, display:'flex', alignItems:'center', justifyContent:'center', fontSize:12, fontWeight:800, color:'#fff' }}>✓</div>}
                <div style={{ fontSize:36, marginBottom:10 }}>{mode.icon}</div>
                <div style={{ fontSize:18, fontWeight:900, color:'#fff', marginBottom:14 }}>{mode.title}</div>
                <div style={{ marginBottom:10 }}>
                  {mode.pros.map((p,i)=>(
                    <div key={i} style={{ fontSize:13, color:'rgba(255,255,255,0.75)', marginBottom:4, display:'flex', gap:8 }}>
                      <span style={{ color:'#10B981' }}>✓</span>{p}
                    </div>
                  ))}
                </div>
                <div style={{ marginBottom:14 }}>
                  {mode.cons.map((c,i)=>(
                    <div key={i} style={{ fontSize:12, color:'rgba(255,255,255,0.35)', marginBottom:3, display:'flex', gap:8 }}>
                      <span>⚠️</span>{c}
                    </div>
                  ))}
                </div>
                <div style={{ fontSize:11, color:mode.color, fontWeight:700, background:`${mode.color}18`, borderRadius:8, padding:'5px 10px' }}>Best for: {mode.best}</div>
              </div>
            );
          })}
        </div>

        <div style={{ background:'rgba(255,255,255,0.06)', borderRadius:12, padding:'12px 16px', marginBottom:24, fontSize:12, color:'rgba(255,255,255,0.5)', textAlign:'center', border:'1px solid rgba(255,255,255,0.08)' }}>
          💡 You can convert Local runs to Account later — see storage transition flow for scope and limits
        </div>

        <div style={{ display:'flex', gap:12 }}>
          <Btn variant='ghost' style={{ color:'rgba(255,255,255,0.4)' }} onClick={()=>setStep('welcome')}>← Back</Btn>
          <Btn variant='primary' size='lg' style={{ flex:1, justifyContent:'center' }} disabled={!storageMode} onClick={()=>setStep('profile')}>
            Continue with {storageMode ? (storageMode==='local'?'🟠 Local':'🟣 Account') : '…'} →
          </Btn>
        </div>
      </div>
    </div>
  );

  // ── Profile Setup ────────────────────────────────────────────────────────
  if (step === 'profile') return (
    <div style={{ minHeight:'100vh', background:'linear-gradient(135deg,#0A0620,#150D35)', display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
      <div style={{ maxWidth:520, width:'100%' }}>
        <StepProgress />
        <div style={{ textAlign:'center', marginBottom:28 }}>
          <div style={{ fontSize:24, fontWeight:900, color:'#fff', marginBottom:6 }}>Set Up Your Profile</div>
          <div style={{ fontSize:14, color:'rgba(255,255,255,0.5)' }}>How should we address you?</div>
        </div>

        {/* Username */}
        <div style={{ marginBottom:24 }}>
          <label style={{ fontSize:13, fontWeight:700, color:'rgba(255,255,255,0.6)', display:'block', marginBottom:8 }}>Trainer Name</label>
          <input
            value={username}
            onChange={e=>setUsername(e.target.value)}
            placeholder="Enter your trainer name…"
            maxLength={32}
            style={{ width:'100%', padding:'14px 18px', borderRadius:14, border:`2px solid ${username?'#E879A0':'rgba(255,255,255,0.1)'}`, background:'rgba(255,255,255,0.07)', fontFamily:'Nunito,sans-serif', fontSize:16, fontWeight:700, color:'#fff', outline:'none', boxSizing:'border-box', transition:'border .2s' }}
          />
        </div>

        {/* Avatar */}
        <div style={{ marginBottom:32 }}>
          <label style={{ fontSize:13, fontWeight:700, color:'rgba(255,255,255,0.6)', display:'block', marginBottom:12 }}>Choose Avatar</label>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(6,1fr)', gap:10 }}>
            {AVATARS.map(av=>{
              const sel = avatar?.id === av.id;
              return (
                <div key={av.id} onClick={()=>setAvatar(av)} style={{ borderRadius:14, border:`2px solid ${sel?'#E879A0':'rgba(255,255,255,0.1)'}`, background:sel?'rgba(232,121,160,0.15)':'rgba(255,255,255,0.05)', padding:'12px 6px', cursor:'pointer', textAlign:'center', transition:'all .15s' }}>
                  <div style={{ fontSize:28, marginBottom:4 }}>{av.icon}</div>
                  <div style={{ fontSize:9, fontWeight:700, color:sel?'#F9A8D4':'rgba(255,255,255,0.4)', lineHeight:1.2 }}>{av.label.split(' ')[0]}</div>
                </div>
              );
            })}
          </div>
        </div>

        <div style={{ display:'flex', gap:12 }}>
          <Btn variant='ghost' style={{ color:'rgba(255,255,255,0.4)' }} onClick={()=>setStep('storage')}>← Back</Btn>
          <Btn variant='primary' size='lg' style={{ flex:1, justifyContent:'center' }} onClick={()=>setStep('preferences')}>
            Continue →
          </Btn>
        </div>
        <div style={{ textAlign:'center', marginTop:12 }}>
          <button onClick={()=>setStep('preferences')} style={{ background:'none', border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, color:'rgba(255,255,255,0.3)', fontWeight:600 }}>Skip for now</button>
        </div>
      </div>
    </div>
  );

  // ── Preferences ──────────────────────────────────────────────────────────
  if (step === 'preferences') return (
    <div style={{ minHeight:'100vh', background:'linear-gradient(135deg,#0A0620,#150D35)', display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
      <div style={{ maxWidth:480, width:'100%' }}>
        <StepProgress />
        <div style={{ textAlign:'center', marginBottom:28 }}>
          <div style={{ fontSize:24, fontWeight:900, color:'#fff', marginBottom:6 }}>Preferences</div>
          <div style={{ fontSize:14, color:'rgba(255,255,255,0.5)' }}>Quick setup — you can change these any time in Settings</div>
        </div>

        <div style={{ display:'flex', flexDirection:'column', gap:14, marginBottom:32 }}>
          {/* Theme */}
          <div style={{ background:'rgba(255,255,255,0.06)', borderRadius:14, padding:'16px 18px', border:'1px solid rgba(255,255,255,0.08)' }}>
            <div style={{ fontSize:13, fontWeight:700, color:'rgba(255,255,255,0.7)', marginBottom:10 }}>🎨 Theme</div>
            <div style={{ display:'flex', gap:8 }}>
              {[['system','💻 System'],['light','☀️ Light'],['dark','🌙 Dark']].map(([val,label])=>(
                <button key={val} onClick={()=>setTheme(val)} style={{ flex:1, padding:'8px', borderRadius:10, border:`1px solid ${theme===val?'#C4B5FD':'rgba(255,255,255,0.1)'}`, background:theme===val?'rgba(124,58,237,0.25)':'transparent', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, color:theme===val?'#C4B5FD':'rgba(255,255,255,0.4)' }}>{label}</button>
              ))}
            </div>
          </div>

          {/* Language */}
          <div style={{ background:'rgba(255,255,255,0.06)', borderRadius:14, padding:'16px 18px', border:'1px solid rgba(255,255,255,0.08)' }}>
            <div style={{ fontSize:13, fontWeight:700, color:'rgba(255,255,255,0.7)', marginBottom:10 }}>🌐 Language</div>
            <div style={{ display:'flex', gap:8 }}>
              {[['en','English'],['ja','日本語']].map(([val,label])=>(
                <button key={val} onClick={()=>setLanguage(val)} style={{ flex:1, padding:'8px', borderRadius:10, border:`1px solid ${language===val?'#C4B5FD':'rgba(255,255,255,0.1)'}`, background:language===val?'rgba(124,58,237,0.25)':'transparent', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, color:language===val?'#C4B5FD':'rgba(255,255,255,0.4)' }}>{label}</button>
              ))}
            </div>
          </div>

          {/* AI model */}
          <div style={{ background:'rgba(255,255,255,0.06)', borderRadius:14, padding:'16px 18px', border:'1px solid rgba(255,255,255,0.08)' }}>
            <div style={{ fontSize:13, fontWeight:700, color:'rgba(255,255,255,0.7)', marginBottom:6 }}>🤖 Default AI Model</div>
            <div style={{ fontSize:11, color:'rgba(255,255,255,0.35)', marginBottom:10 }}>Used for training recommendations and race strategy advice</div>
            <div style={{ display:'flex', gap:8 }}>
              {[['bedrock','☁️ AWS Bedrock'],['ollama','💻 Local Ollama']].map(([val,label])=>(
                <button key={val} onClick={()=>{}} style={{ flex:1, padding:'8px', borderRadius:10, border:`1px solid ${val==='bedrock'?'#C4B5FD':'rgba(255,255,255,0.1)'}`, background:val==='bedrock'?'rgba(124,58,237,0.25)':'transparent', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, color:val==='bedrock'?'#C4B5FD':'rgba(255,255,255,0.4)' }}>{label}</button>
              ))}
            </div>
          </div>
        </div>

        <div style={{ display:'flex', gap:12 }}>
          <Btn variant='ghost' style={{ color:'rgba(255,255,255,0.4)' }} onClick={()=>setStep('profile')}>← Back</Btn>
          <Btn variant='primary' size='lg' style={{ flex:1, justifyContent:'center' }} onClick={()=>tutorialEnabled?setStep('tour'):handleFinish()}>
            {tutorialEnabled ? 'Start Tutorial →' : '🎉 Enter App'}
          </Btn>
        </div>
      </div>
    </div>
  );

  // ── Interactive Dashboard Tour ─────────────────────────────────────────
  if (step === 'tour') {
    const current = TOUR_STEPS[tourStep];
    const isLast = tourStep === TOUR_STEPS.length - 1;
    return (
      <div style={{ minHeight:'100vh', background:'linear-gradient(135deg,#0A0620,#150D35)', display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
        <div style={{ maxWidth:560, width:'100%', textAlign:'center' }}>
          <StepProgress />

          {/* Tour progress */}
          <div style={{ fontSize:12, fontWeight:700, color:'rgba(255,255,255,0.4)', marginBottom:24 }}>
            Feature {tourStep+1} of {TOUR_STEPS.length}
          </div>

          {/* Feature card */}
          <div style={{ background:'rgba(255,255,255,0.07)', borderRadius:24, padding:'40px 32px', marginBottom:28, border:'1px solid rgba(255,255,255,0.1)', position:'relative', overflow:'hidden' }}>
            <div style={{ position:'absolute', top:-40, right:-40, width:160, height:160, borderRadius:'50%', background:'rgba(232,121,160,0.08)', pointerEvents:'none' }}/>
            <div style={{ fontSize:64, marginBottom:20 }}>{current.icon}</div>
            <div style={{ fontSize:26, fontWeight:900, color:'#fff', marginBottom:12 }}>{current.title}</div>
            <div style={{ fontSize:15, color:'rgba(255,255,255,0.65)', lineHeight:1.7, maxWidth:400, margin:'0 auto' }}>{current.desc}</div>
          </div>

          {/* Dot navigation */}
          <div style={{ display:'flex', gap:8, justifyContent:'center', marginBottom:28 }}>
            {TOUR_STEPS.map((_,i)=>(
              <div key={i} onClick={()=>setTourStep(i)} style={{ width: i===tourStep?24:8, height:8, borderRadius:99, background:i<tourStep?'#10B981':i===tourStep?'linear-gradient(90deg,#E879A0,#7C3AED)':'rgba(255,255,255,0.2)', cursor:'pointer', transition:'all .3s' }}/>
            ))}
          </div>

          <div style={{ display:'flex', gap:12 }}>
            {tourStep > 0 && <Btn variant='ghost' style={{ color:'rgba(255,255,255,0.4)' }} onClick={()=>setTourStep(t=>t-1)}>← Prev</Btn>}
            <Btn variant={isLast?'gold':'primary'} size='lg' style={{ flex:1, justifyContent:'center' }}
              onClick={()=>isLast?handleFinish():setTourStep(t=>t+1)}>
              {isLast ? '🎉 Enter Uma Planner!' : `Next: ${TOUR_STEPS[tourStep+1]?.title} →`}
            </Btn>
          </div>
          <div style={{ marginTop:14 }}>
            <button onClick={handleFinish} style={{ background:'none', border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, color:'rgba(255,255,255,0.3)', fontWeight:600 }}>Skip tour</button>
          </div>
        </div>
      </div>
    );
  }

  return null;
}

Object.assign(window, { Onboarding });
