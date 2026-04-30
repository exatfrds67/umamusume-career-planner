
// ── OCR Upload ───────────────────────────────────────────────────────────────
function OCRUpload() {
  const [dragging, setDragging] = React.useState(false);
  const [file, setFile] = React.useState(null);
  const [pipelineStep, setPipelineStep] = React.useState(null);
  const [circuitBreaker, setCircuitBreaker] = React.useState('closed'); // closed | open | half-open
  const [editedValues, setEditedValues] = React.useState({});
  const [applied, setApplied] = React.useState(false);

  const PIPELINE_STEPS = [
    { id:'preprocess', label:'Image Preprocessor',  icon:'🖼️',  desc:'Resize, denoise, contrast boost',       duration:400  },
    { id:'tesseract',  label:'Tesseract OCR Engine', icon:'🔍',  desc:'Text region detection & extraction',    duration:700  },
    { id:'parse',      label:'OCR Data Parser',      icon:'📋',  desc:'Field mapping & value normalization',   duration:400  },
    { id:'validate',   label:'OCR Validator',        icon:'✅',  desc:'Stat range & schema validation',        duration:300  },
    { id:'done',       label:'Ready to Apply',       icon:'🎯',  desc:'Extraction complete',                   duration:0    },
  ];

  const EXTRACTED = [
    { field:'Speed',   key:'speed',   value:528, confidence:98, valid:true  },
    { field:'Stamina', key:'stamina', value:482, confidence:97, valid:true  },
    { field:'Power',   key:'power',   value:445, confidence:95, valid:true  },
    { field:'Guts',    key:'guts',    value:461, confidence:99, valid:true  },
    { field:'Wit',     key:'wit',     value:453, confidence:96, valid:true  },
    { field:'Energy',  key:'energy',  value:'78%', confidence:94, valid:true },
    { field:'Turn',    key:'turn',    value:45,  confidence:100, valid:true  },
  ];

  const runPipeline = () => {
    let stepIdx = 0;
    const advance = () => {
      if (stepIdx >= PIPELINE_STEPS.length) return;
      const step = PIPELINE_STEPS[stepIdx];
      setPipelineStep(step.id);
      stepIdx++;
      if (step.duration > 0) setTimeout(advance, step.duration);
    };
    advance();
  };

  const handleDrop = (e) => {
    e.preventDefault(); setDragging(false);
    const f = e.dataTransfer.files[0];
    if (f) { setFile(f); setEditedValues({}); setApplied(false); runPipeline(); }
  };

  const handleClick = () => {
    setFile({ name:'screenshot_turn45.png' });
    setEditedValues({});
    setApplied(false);
    runPipeline();
  };

  const currentStepIdx = PIPELINE_STEPS.findIndex(s=>s.id===pipelineStep);
  const isDone = pipelineStep === 'done';

  return (
    <div style={{ maxWidth:760 }}>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>OCR Screenshot Upload</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>Upload a game screenshot to extract stat data · Tesseract OCR pipeline · TECH-FLOW-007</div>

      {/* Circuit breaker status */}
      <Card style={{ padding:14, marginBottom:20, display:'flex', gap:14, alignItems:'center', background: circuitBreaker==='closed'?'#F0FDF4':circuitBreaker==='open'?'#FEF2F2':'#FFFBEB', border:`1px solid ${circuitBreaker==='closed'?'#6EE7B7':circuitBreaker==='open'?'#FECACA':'#FCD34D'}` }}>
        <div style={{ width:12, height:12, borderRadius:99, background:circuitBreaker==='closed'?'#10B981':circuitBreaker==='open'?'#EF4444':'#F59E0B', flexShrink:0, boxShadow:`0 0 6px ${circuitBreaker==='closed'?'#10B981':circuitBreaker==='open'?'#EF4444':'#F59E0B'}` }}/>
        <div style={{ flex:1 }}>
          <span style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>CircuitBreaker: </span>
          <span style={{ fontSize:13, fontWeight:900, color:circuitBreaker==='closed'?'#10B981':circuitBreaker==='open'?'#EF4444':'#F59E0B', textTransform:'uppercase' }}>{circuitBreaker}</span>
          <span style={{ fontSize:12, color:'#7C6FAB', marginLeft:8 }}>{circuitBreaker==='closed'?'External APIs healthy · OCR pipeline available':circuitBreaker==='open'?'External API unavailable · Requests blocked':'Testing connectivity · Limited requests'}</span>
        </div>
        <div style={{ display:'flex', gap:6 }}>
          {['closed','open','half-open'].map(s=>(
            <button key={s} onClick={()=>setCircuitBreaker(s)} style={{ padding:'4px 10px', borderRadius:6, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:10, fontWeight:800, background:circuitBreaker===s?'#1E1033':'#EDE9FE', color:circuitBreaker===s?'#fff':'#7C6FAB', textTransform:'capitalize' }}>{s}</button>
          ))}
        </div>
      </Card>

      {/* Drop zone */}
      {!file && (
        <div onDrop={handleDrop} onDragOver={e=>{e.preventDefault();setDragging(true)}} onDragLeave={()=>setDragging(false)}
          style={{ border:`2px dashed ${dragging?'#E879A0':'#C4B5FD'}`, borderRadius:20, padding:'60px 40px', textAlign:'center', background:dragging?'#FDF2F8':'#F9F5FF', transition:'all .2s', cursor:'pointer', marginBottom:20 }}
          onClick={handleClick}>
          <div style={{ fontSize:48, marginBottom:12 }}>📸</div>
          <div style={{ fontSize:18, fontWeight:800, color:'#1E1033', marginBottom:6 }}>Drop your screenshot here</div>
          <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>or click to simulate · PNG, JPG, WEBP · ImagePreprocessor → Tesseract → Parser → Validator</div>
          <Btn variant='primary'>Select File</Btn>
        </div>
      )}

      {/* Pipeline progress */}
      {file && !isDone && (
        <Card style={{ padding:24, marginBottom:20 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:16 }}>OCR Pipeline · {file.name}</div>
          <div style={{ display:'flex', flexDirection:'column', gap:10 }}>
            {PIPELINE_STEPS.filter(s=>s.id!=='done').map((step, i) => {
              const isActive = pipelineStep === step.id;
              const isDoneStep = currentStepIdx > i;
              return (
                <div key={step.id} style={{ display:'flex', gap:12, alignItems:'center', padding:'12px 16px', borderRadius:12, background:isActive?'linear-gradient(135deg,#F5F3FF,#EDE9FE)':isDoneStep?'#F0FDF4':'#F9F5FF', border:`1px solid ${isActive?'#C4B5FD':isDoneStep?'#6EE7B7':'#EDE9FE'}`, transition:'all .3s' }}>
                  <div style={{ width:36, height:36, borderRadius:10, background:isActive?'linear-gradient(135deg,#E879A0,#7C3AED)':isDoneStep?'#10B981':'#EDE9FE', display:'flex', alignItems:'center', justifyContent:'center', fontSize:18, flexShrink:0 }}>
                    {isDoneStep ? '✓' : step.icon}
                  </div>
                  <div style={{ flex:1 }}>
                    <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{step.label}</div>
                    <div style={{ fontSize:11, color:'#7C6FAB' }}>{step.desc}</div>
                  </div>
                  {isActive && (
                    <div style={{ width:60, height:5, background:'#EDE9FE', borderRadius:99, overflow:'hidden' }}>
                      <div style={{ height:'100%', width:'60%', background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99, animation:'slide 1s ease-in-out infinite' }}/>
                    </div>
                  )}
                  {isDoneStep && <span style={{ fontSize:11, fontWeight:800, color:'#10B981' }}>Done</span>}
                </div>
              );
            })}
          </div>
        </Card>
      )}

      {/* Extraction results */}
      {isDone && (
        <Card style={{ padding:24 }}>
          <div style={{ display:'flex', gap:10, alignItems:'center', marginBottom:20 }}>
            <div style={{ width:44, height:44, borderRadius:12, background:'#D1FAE5', display:'flex', alignItems:'center', justifyContent:'center', fontSize:24 }}>✅</div>
            <div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033' }}>Extraction Complete · Validator passed</div>
              <div style={{ fontSize:12, color:'#10B981' }}>{file?.name} · {EXTRACTED.length} fields · all values in valid stat ranges</div>
            </div>
            <button onClick={()=>{setFile(null);setPipelineStep(null);setApplied(false);}} style={{ marginLeft:'auto', background:'#F9F5FF', border:'1px solid #EDE9FE', borderRadius:8, padding:'6px 12px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, color:'#7C6FAB' }}>New Upload</button>
          </div>

          <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB', marginBottom:10 }}>EXTRACTED VALUES — click to edit</div>
          <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8, marginBottom:20 }}>
            {EXTRACTED.map(row=>{
              const edited = editedValues[row.key];
              const displayVal = edited !== undefined ? edited : row.value;
              return (
                <div key={row.field} style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'10px 14px', background:'#F9F5FF', borderRadius:10, border:`1px solid ${edited!==undefined?'#E879A0':'#EDE9FE'}` }}>
                  <span style={{ fontSize:13, fontWeight:700, color:'#7C6FAB' }}>{row.field}</span>
                  <div style={{ display:'flex', gap:8, alignItems:'center' }}>
                    <input
                      value={displayVal}
                      onChange={e=>setEditedValues(v=>({...v,[row.key]:e.target.value}))}
                      style={{ width:60, padding:'3px 8px', borderRadius:6, border:`1px solid ${edited!==undefined?'#E879A0':'#EDE9FE'}`, fontFamily:'Nunito,sans-serif', fontSize:14, fontWeight:900, color:'#1E1033', background:'#fff', textAlign:'right', outline:'none' }}
                    />
                    <span style={{ fontSize:10, fontWeight:800, color:row.confidence>=95?'#10B981':'#F59E0B', background:row.confidence>=95?'#D1FAE5':'#FEF3C7', borderRadius:6, padding:'1px 6px' }}>{row.confidence}%</span>
                  </div>
                </div>
              );
            })}
          </div>

          {applied ? (
            <div style={{ background:'#D1FAE5', borderRadius:12, padding:'14px', textAlign:'center', fontSize:14, fontWeight:800, color:'#065F46' }}>
              ✅ Stats applied to Mejiro Ardan (Turn 45) · StorageMode::ACCOUNT
            </div>
          ) : (
            <Btn variant='primary' style={{ width:'100%', justifyContent:'center' }} onClick={()=>setApplied(true)}>
              Apply to Mejiro Ardan (Turn 45) · StorageMode::ACCOUNT
            </Btn>
          )}
        </Card>
      )}
    </div>
  );
}

// ── Settings ─────────────────────────────────────────────────────────────────
// ── Storage Mode Transition (SEQ-017) ────────────────────────────────────────
function StorageModeTransition({ currentMode, onClose }) {
  const [step, setStep] = React.useState('detect'); // detect | validate | auth | convert | done | error
  const [keepLocal, setKeepLocal] = React.useState(true);
  const [progress, setProgress] = React.useState(0);
  const [validationResult, setValidationResult] = React.useState(null);
  const [authEmail, setAuthEmail] = React.useState('');

  const LOCAL_KEYS = ['ucp_characters', 'ucp_careers', 'ucp_skill_builds'];
  const LOCAL_COUNTS = { characters:3, careers:3, skill_builds:2 };

  const runValidation = () => {
    setStep('validate');
    setTimeout(() => {
      setValidationResult({ ok:true, items:LOCAL_COUNTS, checksum:'✅ Valid', uuids:'✅ All present', stats:'✅ In range' });
    }, 900);
  };

  const runConvert = () => {
    setStep('convert'); setProgress(0);
    const interval = setInterval(() => {
      setProgress(p => { if (p>=100){ clearInterval(interval); setStep('done'); return 100; } return p+12; });
    }, 220);
  };

  const stepLabel = { detect:'Detect Mode', validate:'Validate', auth:'Sign In', convert:'Convert', done:'Done', error:'Error' };

  return (
    <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.85)', backdropFilter:'blur(10px)', zIndex:200, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
      <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:520, overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.35)', maxHeight:'90vh', overflowY:'auto' }}>
        <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', padding:'20px 24px' }}>
          <div style={{ fontSize:20, fontWeight:900, color:'#fff' }}>Storage Mode Transition</div>
          <div style={{ fontSize:13, color:'rgba(255,255,255,0.5)', marginTop:2 }}>🟠 Local → 🟣 Account · DetectStorageMode</div>
          {/* Step indicators */}
          <div style={{ display:'flex', gap:4, marginTop:14, alignItems:'center' }}>
            {['detect','validate','auth','convert','done'].map((s,i,arr)=>(
              <React.Fragment key={s}>
                <div style={{ textAlign:'center' }}>
                  <div style={{ width:24, height:24, borderRadius:99, background:step===s?'#E879A0':['done','convert','auth','validate'].indexOf(step)>['done','convert','auth','validate'].indexOf(s)?'#10B981':'rgba(255,255,255,0.15)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:11, fontWeight:800, color:'#fff', margin:'0 auto 3px' }}>
                    {['done','convert','auth','validate'].indexOf(step)>['done','convert','auth','validate'].indexOf(s)?'✓':(i+1)}
                  </div>
                  <div style={{ fontSize:9, color:'rgba(255,255,255,0.5)', whiteSpace:'nowrap' }}>{stepLabel[s]}</div>
                </div>
                {i<arr.length-1 && <div style={{ flex:1, height:1, background:'rgba(255,255,255,0.15)', marginBottom:12 }}/>}
              </React.Fragment>
            ))}
          </div>
        </div>

        <div style={{ padding:24 }}>
          {step==='detect' && (
            <>
              <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Mode Detection · StorageMode Resolution</div>
              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:10, marginBottom:16 }}>
                {[
                  { label:'Current Mode', val:'🟠 Local', color:'#F59E0B', bg:'#FFFBEB' },
                  { label:'Target Mode', val:'🟣 Account', color:'#7C3AED', bg:'#F5F3FF' },
                  { label:'Auth Status', val:'Not signed in', color:'#EF4444', bg:'#FEF2F2' },
                  { label:'Local Keys Found', val:LOCAL_KEYS.length+' of '+LOCAL_KEYS.length, color:'#10B981', bg:'#F0FDF4' },
                ].map(item=>(
                  <div key={item.label} style={{ background:item.bg, borderRadius:10, padding:'10px 12px' }}>
                    <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.label}</div>
                    <div style={{ fontSize:14, fontWeight:800, color:item.color, marginTop:2 }}>{item.val}</div>
                  </div>
                ))}
              </div>
              <div style={{ background:'#F9F5FF', borderRadius:12, padding:'12px 14px', marginBottom:16, fontSize:12, color:'#7C6FAB' }}>
                <strong style={{color:'#1E1033'}}>Browser keys read:</strong> {LOCAL_KEYS.join(', ')}
              </div>
              <div style={{ display:'flex', gap:10 }}>
                <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={runValidation}>Validate Local Data →</Btn>
                <Btn variant='ghost' onClick={onClose}>Cancel</Btn>
              </div>
            </>
          )}

          {step==='validate' && !validationResult && (
            <div style={{ textAlign:'center', padding:'30px 0' }}>
              <div style={{ fontSize:36, marginBottom:12 }}>🔍</div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033' }}>Validating local payloads…</div>
              <div style={{ fontSize:12, color:'#7C6FAB', marginTop:4 }}>UUID check · payload structure · stat ranges · checksum</div>
            </div>
          )}

          {step==='validate' && validationResult && (
            <>
              <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Validation Results · LocalStorageService</div>
              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8, marginBottom:14 }}>
                {Object.entries(LOCAL_COUNTS).map(([k,v])=>(
                  <div key={k} style={{ textAlign:'center', background:'#F9F5FF', borderRadius:10, padding:'10px' }}>
                    <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700, textTransform:'capitalize' }}>{k.replace('_',' ')}</div>
                    <div style={{ fontSize:22, fontWeight:900, color:'#7C3AED' }}>{v}</div>
                  </div>
                ))}
              </div>
              {[
                { label:'UUID integrity', val:validationResult.uuids },
                { label:'Stat ranges', val:validationResult.stats },
                { label:'Checksum', val:validationResult.checksum },
                { label:'Duplicate check', val:'✅ No conflicts' },
              ].map(row=>(
                <div key={row.label} style={{ display:'flex', justifyContent:'space-between', padding:'8px 0', borderBottom:'1px solid #F9F5FF' }}>
                  <span style={{ fontSize:13, color:'#7C6FAB' }}>{row.label}</span>
                  <span style={{ fontSize:13, fontWeight:800, color:'#10B981' }}>{row.val}</span>
                </div>
              ))}
              <div style={{ display:'flex', gap:10, marginTop:16 }}>
                <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={()=>setStep('auth')}>Sign In to Convert →</Btn>
                <Btn variant='ghost' onClick={onClose}>Cancel</Btn>
              </div>
            </>
          )}

          {step==='auth' && (
            <>
              <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Sign In to Account</div>
              <div style={{ marginBottom:12 }}>
                <label style={{ fontSize:12, fontWeight:700, color:'#7C6FAB', display:'block', marginBottom:4 }}>Email</label>
                <input value={authEmail} onChange={e=>setAuthEmail(e.target.value)} placeholder="trainer@example.com" style={{ width:'100%', padding:'10px 14px', borderRadius:10, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:13, outline:'none', boxSizing:'border-box' }}/>
              </div>
              <div style={{ marginBottom:16 }}>
                <label style={{ fontSize:12, fontWeight:700, color:'#7C6FAB', display:'block', marginBottom:4 }}>Password</label>
                <input type="password" placeholder="••••••••" style={{ width:'100%', padding:'10px 14px', borderRadius:10, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:13, outline:'none', boxSizing:'border-box' }}/>
              </div>
              <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'10px 12px', background:'#F9F5FF', borderRadius:10, marginBottom:14 }}>
                <span style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>Keep local copy after conversion</span>
                <div onClick={()=>setKeepLocal(!keepLocal)} style={{ width:40, height:22, borderRadius:99, background:keepLocal?'linear-gradient(135deg,#E879A0,#7C3AED)':'#D1D5DB', cursor:'pointer', position:'relative', transition:'background .2s' }}>
                  <div style={{ position:'absolute', top:2, left:keepLocal?20:2, width:18, height:18, borderRadius:99, background:'#fff', transition:'left .2s' }}/>
                </div>
              </div>
              <div style={{ display:'flex', gap:10 }}>
                <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={runConvert}>Convert to Account Mode →</Btn>
                <Btn variant='ghost' onClick={()=>setStep('validate')}>← Back</Btn>
              </div>
            </>
          )}

          {step==='convert' && (
            <div style={{ textAlign:'center', padding:'20px 0' }}>
              <div style={{ fontSize:40, marginBottom:16 }}>⚙️</div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:4 }}>Converting…</div>
              <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:20 }}>batchConvertToAccount · Writing Character rows with local_uuid</div>
              <div style={{ height:10, background:'#EDE9FE', borderRadius:99, marginBottom:8, overflow:'hidden' }}>
                <div style={{ height:'100%', width:`${progress}%`, background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99, transition:'width .22s ease' }}/>
              </div>
              <div style={{ fontSize:12, color:'#7C6FAB' }}>{Math.round(progress/100*8)} of 8 records written</div>
            </div>
          )}

          {step==='done' && (
            <>
              <div style={{ textAlign:'center', marginBottom:20 }}>
                <div style={{ fontSize:48, marginBottom:10 }}>🎉</div>
                <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>Conversion Complete!</div>
                <div style={{ fontSize:13, color:'#7C6FAB', marginTop:4 }}>Storage mode switched to 🟣 Account</div>
              </div>
              <div style={{ background:'#D1FAE5', borderRadius:12, padding:'14px', marginBottom:14 }}>
                {MOCK_CHARACTERS.map((c,i)=>(
                  <div key={i} style={{ display:'flex', justifyContent:'space-between', padding:'4px 0', fontSize:13 }}>
                    <span style={{ color:'#065F46', fontWeight:700 }}>{c.name}</span>
                    <span style={{ color:'#10B981', fontWeight:800 }}>✓ local_uuid linked</span>
                  </div>
                ))}
              </div>
              {keepLocal && <div style={{ background:'#FEF3C7', borderRadius:10, padding:'8px 12px', marginBottom:14, fontSize:12, color:'#92400E', fontWeight:700 }}>🟠 Local copy retained in browser storage</div>}
              <Btn variant='primary' style={{ width:'100%', justifyContent:'center' }} onClick={onClose}>Done</Btn>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

function Settings() {
  const [storageMode, setStorageMode] = React.useState('account');
  const [showTransition, setShowTransition] = React.useState(false);
  const [theme, setTheme] = React.useState('light');
  const [aiModel, setAiModel] = React.useState('bedrock');
  const [notifications, setNotifications] = React.useState(true);
  const [compactMode, setCompactMode] = React.useState(false);

  const Toggle = ({ value, onChange }) => (
    <div onClick={()=>onChange(!value)} style={{ width:44, height:24, borderRadius:99, background:value?'linear-gradient(135deg,#E879A0,#7C3AED)':'#D1D5DB', cursor:'pointer', position:'relative', transition:'background .2s', flexShrink:0 }}>
      <div style={{ position:'absolute', top:2, left:value?22:2, width:20, height:20, borderRadius:99, background:'#fff', transition:'left .2s', boxShadow:'0 1px 4px rgba(0,0,0,0.2)' }}/>
    </div>
  );

  const Section = ({ title, children }) => (
    <div style={{ marginBottom:28 }}>
      <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB', textTransform:'uppercase', letterSpacing:1, marginBottom:14, paddingBottom:8, borderBottom:'1px solid #EDE9FE' }}>{title}</div>
      {children}
    </div>
  );

  const Row = ({ label, sub, children }) => (
    <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'12px 0', borderBottom:'1px solid #F9F5FF' }}>
      <div>
        <div style={{ fontSize:14, fontWeight:700, color:'#1E1033' }}>{label}</div>
        {sub && <div style={{ fontSize:12, color:'#7C6FAB' }}>{sub}</div>}
      </div>
      {children}
    </div>
  );

  return (
    <div style={{ maxWidth:640 }}>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:24 }}>Settings</div>
      <Card style={{ padding:24 }}>
        <Section title="Storage">
          <Row label="Storage Mode" sub="Where your career run data is saved">
            <div style={{ display:'flex', gap:6 }}>
              {['local','account'].map(m=>(
                <button key={m} onClick={()=>setStorageMode(m)} style={{ padding:'6px 14px', borderRadius:8, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, textTransform:'capitalize', background:storageMode===m?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:storageMode===m?'#fff':'#7C6FAB' }}>{m==='local'?'🟠 Local':'🟣 Account'}</button>
              ))}
            </div>
          </Row>
          {storageMode==='local' && (
            <div style={{ marginTop:8, padding:'12px 14px', background:'linear-gradient(135deg,#FFFBEB,#FEF3C7)', borderRadius:10, border:'1px solid #FCD34D', display:'flex', justifyContent:'space-between', alignItems:'center' }}>
              <div>
                <div style={{ fontSize:13, fontWeight:700, color:'#92400E' }}>Switch to Account Mode</div>
                <div style={{ fontSize:11, color:'#92400E', opacity:.8 }}>Convert your local data for cross-device sync</div>
              </div>
              <Btn variant='gold' size='sm' onClick={()=>setShowTransition(true)}>Convert →</Btn>
            </div>
          )}
        </Section>

        <Section title="Onboarding">
          <Row label="Replay Onboarding" sub="Go through the welcome flow again">
            <Btn variant='secondary' size='sm' onClick={()=>{ localStorage.removeItem('uma_onboarded'); window.location.reload(); }}>
              Replay →
            </Btn>
          </Row>
        </Section>

        <Section title="AI Advisor">
          <Row label="Default AI Model" sub="Model used for recommendations">
            <select value={aiModel} onChange={e=>setAiModel(e.target.value)} style={{ padding:'7px 12px', borderRadius:10, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, color:'#7C3AED', background:'#F9F5FF', cursor:'pointer', outline:'none' }}>
              <option value='bedrock'>☁️ AWS Bedrock Claude</option>
              <option value='ollama'>💻 Local Ollama</option>
            </select>
          </Row>
        </Section>

        <Section title="Appearance">
          <Row label="Theme" sub="Light or dark mode">
            <div style={{ display:'flex', gap:6 }}>
              {['light','dark'].map(t=>(
                <button key={t} onClick={()=>setTheme(t)} style={{ padding:'6px 14px', borderRadius:8, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, textTransform:'capitalize', background:theme===t?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:theme===t?'#fff':'#7C6FAB' }}>{t==='light'?'☀️ Light':'🌙 Dark'}</button>
              ))}
            </div>
          </Row>
          <Row label="Compact Mode" sub="Reduce spacing for more data density">
            <Toggle value={compactMode} onChange={setCompactMode} />
          </Row>
        </Section>

        <Section title="Notifications">
          <Row label="In-App Notifications" sub="Training reminders, race alerts, goal warnings">
            <Toggle value={notifications} onChange={setNotifications} />
          </Row>
        </Section>

        <div style={{ display:'flex', gap:10, marginTop:8 }}>
          <Btn variant='primary'>Save Settings</Btn>
          <Btn variant='secondary'>Reset to Defaults</Btn>
        </div>
      </Card>
      {showTransition && <StorageModeTransition currentMode={storageMode} onClose={()=>setShowTransition(false)}/>}
    </div>
  );
}

// ── Performance / MCP / Admin stubs ──────────────────────────────────────────
function StubScreen({ icon, title, description, stats=[] }) {
  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>{title}</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:24 }}>{description}</div>
      {stats.length>0 && (
        <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(180px,1fr))', gap:14, marginBottom:24 }}>
          {stats.map(s=>(
            <Card key={s.label} style={{ padding:20, background:s.bg||'#fff' }}>
              <div style={{ fontSize:11, fontWeight:700, color:'#7C6FAB', marginBottom:4 }}>{s.icon} {s.label}</div>
              <div style={{ fontSize:28, fontWeight:900, color:s.color||'#1E1033' }}>{s.value}</div>
              {s.sub && <div style={{ fontSize:11, color:'#7C6FAB', marginTop:2 }}>{s.sub}</div>}
            </Card>
          ))}
        </div>
      )}
      <Card style={{ padding:60, textAlign:'center' }}>
        <div style={{ fontSize:56, marginBottom:16 }}>{icon}</div>
        <div style={{ fontSize:18, fontWeight:800, color:'#1E1033', marginBottom:6 }}>{title}</div>
        <div style={{ fontSize:13, color:'#7C6FAB', maxWidth:400, margin:'0 auto' }}>{description}</div>
      </Card>
    </div>
  );
}

function Performance() {
  return <StubScreen icon="📊" title="Performance Dashboard" description="System performance metrics, response times, and resource utilization."
    stats={[
      { label:'Avg Response', value:'84ms', icon:'⚡', color:'#10B981', bg:'#F0FDF4' },
      { label:'Uptime', value:'99.9%', icon:'✅', color:'#7C3AED', bg:'#F5F3FF' },
      { label:'API Calls Today', value:'1,284', icon:'🔄', color:'#E879A0', bg:'#FDF2F8' },
      { label:'Cache Hit Rate', value:'94%', icon:'🎯', color:'#F59E0B', bg:'#FFFBEB' },
    ]}
  />;
}

function MCPMonitor() {
  return <StubScreen icon="🔌" title="MCP Monitoring" description="Model Context Protocol server status, agent activity, and integration health."
    stats={[
      { label:'MCP Servers', value:'3', icon:'🖥️', color:'#10B981', bg:'#F0FDF4' },
      { label:'Active Agents', value:'7', icon:'🤖', color:'#7C3AED', bg:'#F5F3FF' },
      { label:'Tasks Today', value:'42', icon:'📋', color:'#E879A0', bg:'#FDF2F8' },
      { label:'Errors', value:'0', icon:'✅', color:'#10B981', bg:'#F0FDF4' },
    ]}
  />;
}

function AdminPanel() {
  return <StubScreen icon="🛡️" title="Admin Panel" description="System administration, user management, configuration, and audit logs."
    stats={[
      { label:'Total Users', value:'1,042', icon:'👥', color:'#7C3AED', bg:'#F5F3FF' },
      { label:'Career Runs', value:'3,891', icon:'🏇', color:'#E879A0', bg:'#FDF2F8' },
      { label:'Storage Used', value:'2.4 GB', icon:'💾', color:'#F59E0B', bg:'#FFFBEB' },
      { label:'System Health', value:'OK', icon:'✅', color:'#10B981', bg:'#F0FDF4' },
    ]}
  />;
}

function DataManagement() {
  const [migrateStep, setMigrateStep] = React.useState(null); // null | 'validate' | 'preview' | 'migrating' | 'done'
  const [keepLocal, setKeepLocal] = React.useState(true);
  const [progress, setProgress] = React.useState(0);

  const LOCAL_ASSETS = { characters: 3, careers: 3, builds: 2 };
  const totalAssets = Object.values(LOCAL_ASSETS).reduce((a,b)=>a+b,0);

  const startMigration = () => {
    setMigrateStep('migrating');
    setProgress(0);
    const interval = setInterval(() => {
      setProgress(p => {
        if (p >= 100) { clearInterval(interval); setMigrateStep('done'); return 100; }
        return p + 14;
      });
    }, 280);
  };

  const MigrationModal = () => {
    if (!migrateStep) return null;
    return (
      <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.8)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
        <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:500, overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.3)' }}>
          <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', padding:'20px 24px' }}>
            <div style={{ fontSize:20, fontWeight:900, color:'#fff' }}>Local → Account Migration</div>
            <div style={{ fontSize:13, color:'rgba(255,255,255,0.5)', marginTop:2 }}>Convert your local browser data to cloud-backed account storage</div>
          </div>

          <div style={{ padding:24 }}>
            {migrateStep === 'validate' && (
              <>
                <div style={{ marginBottom:16 }}>
                  <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:10 }}>Browser Storage Contents</div>
                  <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8, marginBottom:14 }}>
                    {Object.entries(LOCAL_ASSETS).map(([k,v])=>(
                      <div key={k} style={{ textAlign:'center', background:'#F9F5FF', borderRadius:10, padding:'10px' }}>
                        <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700, textTransform:'capitalize' }}>{k}</div>
                        <div style={{ fontSize:22, fontWeight:900, color:'#7C3AED' }}>{v}</div>
                      </div>
                    ))}
                  </div>
                  <div style={{ background:'#D1FAE5', borderRadius:10, padding:'10px 14px', fontSize:13, color:'#065F46', fontWeight:700, marginBottom:14 }}>
                    ✅ Validation passed · {totalAssets} assets ready to convert
                  </div>
                  <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'12px', background:'#F9F5FF', borderRadius:10 }}>
                    <div>
                      <div style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>Keep local copy after migration</div>
                      <div style={{ fontSize:11, color:'#7C6FAB' }}>Your browser data won't be deleted</div>
                    </div>
                    <div onClick={()=>setKeepLocal(!keepLocal)} style={{ width:44, height:24, borderRadius:99, background:keepLocal?'linear-gradient(135deg,#E879A0,#7C3AED)':'#D1D5DB', cursor:'pointer', position:'relative', transition:'background .2s', flexShrink:0 }}>
                      <div style={{ position:'absolute', top:2, left:keepLocal?22:2, width:20, height:20, borderRadius:99, background:'#fff', transition:'left .2s' }}/>
                    </div>
                  </div>
                </div>
                <div style={{ display:'flex', gap:10 }}>
                  <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={()=>setMigrateStep('preview')}>Preview Migration →</Btn>
                  <Btn variant='ghost' onClick={()=>setMigrateStep(null)}>Cancel</Btn>
                </div>
              </>
            )}

            {migrateStep === 'preview' && (
              <>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:12 }}>What will be migrated</div>
                {MOCK_CHARACTERS.map((c,i)=>(
                  <div key={i} style={{ display:'flex', alignItems:'center', gap:12, padding:'10px 14px', background:'#F9F5FF', borderRadius:10, marginBottom:8, border:'1px solid #EDE9FE' }}>
                    <div style={{ width:32, height:32, borderRadius:8, background:'linear-gradient(135deg,#E879A0,#7C3AED)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:14 }}>🐴</div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{c.name}</div>
                      <div style={{ fontSize:11, color:'#7C6FAB' }}>Turn {c.turn} · {c.stage} · {c.scenario}</div>
                    </div>
                    <span style={{ fontSize:11, background:'#D1FAE5', color:'#065F46', borderRadius:6, padding:'2px 8px', fontWeight:800 }}>Ready</span>
                  </div>
                ))}
                <div style={{ background:'#FEF3C7', borderRadius:10, padding:'10px 14px', fontSize:12, color:'#92400E', fontWeight:700, marginBottom:16, marginTop:8 }}>
                  ⚠️ Duplicate check passed · No conflicts found
                </div>
                <div style={{ display:'flex', gap:10 }}>
                  <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={startMigration}>Confirm & Migrate</Btn>
                  <Btn variant='ghost' onClick={()=>setMigrateStep('validate')}>← Back</Btn>
                </div>
              </>
            )}

            {migrateStep === 'migrating' && (
              <div style={{ textAlign:'center', padding:'20px 0' }}>
                <div style={{ fontSize:40, marginBottom:16 }}>⚙️</div>
                <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:6 }}>Migrating data…</div>
                <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>Creating account-backed records. Do not close this window.</div>
                <div style={{ height:10, background:'#EDE9FE', borderRadius:99, marginBottom:8, overflow:'hidden' }}>
                  <div style={{ height:'100%', width:`${progress}%`, background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99, transition:'width .28s ease' }}/>
                </div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>{Math.round(progress/100*totalAssets)}/{totalAssets} assets migrated</div>
              </div>
            )}

            {migrateStep === 'done' && (
              <>
                <div style={{ textAlign:'center', marginBottom:20 }}>
                  <div style={{ fontSize:48, marginBottom:10 }}>🎉</div>
                  <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>Migration Complete!</div>
                  <div style={{ fontSize:13, color:'#7C6FAB', marginTop:4 }}>{totalAssets} assets successfully migrated to Account mode</div>
                </div>
                <div style={{ background:'#D1FAE5', borderRadius:12, padding:'14px', marginBottom:16 }}>
                  {MOCK_CHARACTERS.map((c,i)=>(
                    <div key={i} style={{ display:'flex', justifyContent:'space-between', padding:'4px 0', fontSize:13 }}>
                      <span style={{ color:'#065F46', fontWeight:700 }}>{c.name}</span>
                      <span style={{ color:'#10B981', fontWeight:800 }}>✓ Migrated</span>
                    </div>
                  ))}
                </div>
                <Btn variant='primary' style={{ width:'100%', justifyContent:'center' }} onClick={()=>setMigrateStep(null)}>
                  Done
                </Btn>
              </>
            )}
          </div>
        </div>
      </div>
    );
  };

  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>Data Management</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:24 }}>Import, export, backup, and migrate your career run data.</div>

      {/* Storage conversion CTA */}
      <Card style={{ padding:20, marginBottom:20, background:'linear-gradient(135deg,#F5F3FF,#EDE9FE)', border:'2px solid #C4B5FD' }}>
        <div style={{ display:'flex', alignItems:'center', gap:16, flexWrap:'wrap' }}>
          <div style={{ fontSize:36 }}>🟠→🟣</div>
          <div style={{ flex:1 }}>
            <div style={{ fontSize:16, fontWeight:800, color:'#1E1033' }}>Convert Local Data to Account</div>
            <div style={{ fontSize:13, color:'#7C6FAB', marginTop:2 }}>You have {totalAssets} assets in browser storage. Migrate to your account for cross-device sync and secure backup.</div>
          </div>
          <Btn variant='primary' onClick={()=>setMigrateStep('validate')}>Start Migration →</Btn>
        </div>
      </Card>

      <div style={{ display:'grid', gridTemplateColumns:'repeat(2,1fr)', gap:16 }}>
        {[
          { icon:'⬆️', label:'Export Data', desc:'Download all career runs as JSON or CSV', btn:'Export', variant:'primary' },
          { icon:'⬇️', label:'Import Data', desc:'Restore from a previous backup file', btn:'Import', variant:'secondary' },
          { icon:'☁️', label:'Cloud Backup', desc:'Sync all account data to cloud now', btn:'Backup Now', variant:'gold' },
          { icon:'🔄', label:'Legacy Migration', desc:'Migrate from previous app versions', btn:'Migrate', variant:'secondary' },
        ].map(item=>(
          <Card key={item.label} style={{ padding:22 }}>
            <div style={{ fontSize:32, marginBottom:10 }}>{item.icon}</div>
            <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:4 }}>{item.label}</div>
            <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:16 }}>{item.desc}</div>
            <Btn variant={item.variant} size='sm'>{item.btn}</Btn>
          </Card>
        ))}
      </div>
      <MigrationModal />
    </div>
  );
}

Object.assign(window, { OCRUpload, Settings, Performance, MCPMonitor, AdminPanel, DataManagement });
