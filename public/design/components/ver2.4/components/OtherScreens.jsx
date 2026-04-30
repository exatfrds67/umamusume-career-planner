
// ── OCR Upload ───────────────────────────────────────────────────────────────
function OCRUpload() {
  const [dragging, setDragging] = React.useState(false);
  const [file, setFile] = React.useState(null);
  const [status, setStatus] = React.useState(null); // null | 'processing' | 'done'

  const handleDrop = (e) => {
    e.preventDefault(); setDragging(false);
    const f = e.dataTransfer.files[0];
    if (f) { setFile(f); setStatus('processing'); setTimeout(()=>setStatus('done'),2200); }
  };

  const EXTRACTED = [
    { field:'Speed', value:528, confidence:98 },
    { field:'Stamina', value:482, confidence:97 },
    { field:'Power', value:445, confidence:95 },
    { field:'Guts', value:461, confidence:99 },
    { field:'Wit', value:453, confidence:96 },
    { field:'Energy', value:'78%', confidence:94 },
    { field:'Turn', value:45, confidence:100 },
  ];

  return (
    <div style={{ maxWidth:700 }}>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>OCR Screenshot Upload</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:24 }}>Upload a screenshot to automatically extract stat data using optical character recognition.</div>

      {!file && (
        <div onDrop={handleDrop} onDragOver={e=>{e.preventDefault();setDragging(true)}} onDragLeave={()=>setDragging(false)}
          style={{ border:`2px dashed ${dragging?'#E879A0':'#C4B5FD'}`, borderRadius:20, padding:'60px 40px', textAlign:'center', background:dragging?'#FDF2F8':'#F9F5FF', transition:'all .2s', cursor:'pointer' }}
          onClick={()=>{ setFile({name:'screenshot.png'}); setStatus('processing'); setTimeout(()=>setStatus('done'),2200); }}>
          <div style={{ fontSize:48, marginBottom:16 }}>📸</div>
          <div style={{ fontSize:18, fontWeight:800, color:'#1E1033', marginBottom:6 }}>Drop your screenshot here</div>
          <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>or click to select a file · PNG, JPG, WEBP supported</div>
          <Btn variant='primary'>Select File</Btn>
        </div>
      )}

      {status==='processing' && (
        <Card style={{ padding:40, textAlign:'center' }}>
          <div style={{ fontSize:40, marginBottom:16 }}>⚙️</div>
          <div style={{ fontSize:18, fontWeight:800, color:'#1E1033', marginBottom:6 }}>Processing {file?.name}…</div>
          <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>Running OCR and extracting game data</div>
          <div style={{ width:200, height:6, background:'#EDE9FE', borderRadius:99, margin:'0 auto', overflow:'hidden' }}>
            <div style={{ height:'100%', width:'70%', background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99, animation:'slide 1.4s ease-in-out infinite' }}/>
          </div>
        </Card>
      )}

      {status==='done' && (
        <Card style={{ padding:24 }}>
          <div style={{ display:'flex', gap:10, alignItems:'center', marginBottom:20 }}>
            <div style={{ width:44, height:44, borderRadius:12, background:'#D1FAE5', display:'flex', alignItems:'center', justifyContent:'center', fontSize:24 }}>✅</div>
            <div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033' }}>Extraction Complete</div>
              <div style={{ fontSize:12, color:'#10B981' }}>{file?.name} · 7 fields extracted</div>
            </div>
            <button onClick={()=>{setFile(null);setStatus(null);}} style={{ marginLeft:'auto', background:'#F9F5FF', border:'1px solid #EDE9FE', borderRadius:8, padding:'6px 12px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, color:'#7C6FAB' }}>New Upload</button>
          </div>
          <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8, marginBottom:20 }}>
            {EXTRACTED.map(row=>(
              <div key={row.field} style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'10px 14px', background:'#F9F5FF', borderRadius:10, border:'1px solid #EDE9FE' }}>
                <span style={{ fontSize:13, fontWeight:700, color:'#7C6FAB' }}>{row.field}</span>
                <div style={{ display:'flex', gap:8, alignItems:'center' }}>
                  <span style={{ fontSize:15, fontWeight:900, color:'#1E1033' }}>{row.value}</span>
                  <span style={{ fontSize:10, fontWeight:800, color:'#10B981', background:'#D1FAE5', borderRadius:6, padding:'1px 6px' }}>{row.confidence}%</span>
                </div>
              </div>
            ))}
          </div>
          <Btn variant='primary'>Apply to Mejiro Ardan (Turn 45)</Btn>
        </Card>
      )}
    </div>
  );
}

// ── Settings ─────────────────────────────────────────────────────────────────
function Settings() {
  const [storageMode, setStorageMode] = React.useState('account');
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
