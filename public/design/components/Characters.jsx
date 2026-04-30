
function Characters({ setScreen, setCharacterId, openWizard }) {
  const [view, setView] = React.useState('list'); // 'list' | 'detail'
  const [selected, setSelected] = React.useState(null);
  const [tab, setTab] = React.useState('stats');

  const openDetail = (char) => { setSelected(char); setView('detail'); setTab('stats'); };

  if (view === 'detail' && selected) return <CharacterDetail char={selected} onBack={()=>setView('list')} setScreen={setScreen} />;

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:24 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Characters</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{MOCK_CHARACTERS.length} career runs active</div>
        </div>
        <Btn variant='primary' onClick={openWizard}>+ New Character</Btn>
      </div>

      <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(300px,1fr))', gap:20 }}>
        {MOCK_CHARACTERS.map(char => {
          const totalStats = Object.values(char.stats).reduce((a,b)=>a+b,0);
          const stageColor = char.stage==='Junior Year'?'#3B82F6':char.stage==='Classic Year'?'#7C3AED':'#E879A0';
          return (
            <Card key={char.id} style={{ padding:0, overflow:'hidden', cursor:'pointer', transition:'transform .15s,box-shadow .15s' }}
              onClick={()=>openDetail(char)}>
              {/* Card header gradient */}
              <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', padding:'16px 20px', position:'relative', overflow:'hidden' }}>
                <div style={{ position:'absolute', right:-10, top:-10, width:100, height:100, borderRadius:99, background:'rgba(232,121,160,0.12)' }}/>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start' }}>
                  <div>
                    <div style={{ display:'flex', gap:6, marginBottom:6, alignItems:'center' }}>
                      <RarityBadge rarity={char.rarity} />
                      <span style={{ fontSize:11, color:'rgba(255,255,255,0.5)', fontWeight:700 }}>{char.scenario}</span>
                    </div>
                    <div style={{ fontSize:18, fontWeight:900, color:'#fff' }}>{char.name}</div>
                    <div style={{ fontSize:12, color:'rgba(255,255,255,0.5)', marginTop:2 }}>
                      <span style={{ color:stageColor, fontWeight:700 }}>{char.stage}</span> · Turn {char.turn}/{char.totalTurns}
                    </div>
                  </div>
                  <div style={{ textAlign:'right' }}>
                    <div style={{ fontSize:24, fontWeight:900, color:'#F9A8D4' }}>
                      {Math.round((char.turn/char.totalTurns)*100)}%
                    </div>
                    <div style={{ fontSize:10, color:'rgba(255,255,255,0.4)' }}>complete</div>
                  </div>
                </div>
                <div style={{ marginTop:10, height:4, background:'rgba(255,255,255,0.1)', borderRadius:99 }}>
                  <div style={{ height:'100%', width:`${(char.turn/char.totalTurns)*100}%`, background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99 }}/>
                </div>
              </div>

              {/* Stats mini */}
              <div style={{ padding:'16px 20px' }}>
                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8, marginBottom:14 }}>
                  {Object.entries(char.stats).map(([s,v])=>(
                    <div key={s} style={{ textAlign:'center', background:'#F9F5FF', borderRadius:10, padding:'8px 4px' }}>
                      <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{STAT_ICONS[s]}</div>
                      <div style={{ fontSize:15, fontWeight:900, color:STAT_COLORS[s] }}>{v}</div>
                      <GradeBadge grade={getGrade(v)} />
                    </div>
                  ))}
                </div>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                  <div style={{ display:'flex', gap:6 }}>
                    <MoodChip mood={char.mood} />
                  </div>
                  <div style={{ fontSize:12, color:'#7C6FAB' }}>Total: <span style={{ fontWeight:800, color:'#7C3AED' }}>{totalStats}</span></div>
                </div>
              </div>
            </Card>
          );
        })}

        {/* New character CTA */}
        <div onClick={openWizard} style={{ borderRadius:16, border:'2px dashed #C4B5FD', display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center', padding:'40px 20px', cursor:'pointer', background:'#FAFBFF', minHeight:280, transition:'all .15s' }}>
          <div style={{ width:56, height:56, borderRadius:16, background:'linear-gradient(135deg,#E879A0,#7C3AED)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:28, marginBottom:12 }}>+</div>
          <div style={{ fontSize:15, fontWeight:800, color:'#7C3AED', marginBottom:4 }}>New Character</div>
          <div style={{ fontSize:12, color:'#7C6FAB', textAlign:'center' }}>Start a new career run with the setup wizard</div>
        </div>
      </div>
    </div>
  );
}

function CharacterDetail({ char, onBack, setScreen }) {
  const [tab, setTab] = React.useState('stats');
  const tabs = ['stats','aptitudes','factors','goals','history','snapshots'];

  const AptitudeGrid = ({ label, items }) => (
    <div style={{ marginBottom:16 }}>
      <div style={{ fontSize:11, fontWeight:800, color:'#7C6FAB', textTransform:'uppercase', letterSpacing:1, marginBottom:8 }}>{label}</div>
      <div style={{ display:'flex', gap:8, flexWrap:'wrap' }}>
        {items.map(({ key, label: lbl, grade }) => (
          <div key={key} style={{ background:'#F9F5FF', borderRadius:10, padding:'8px 12px', textAlign:'center', border:'1px solid #EDE9FE' }}>
            <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:600, marginBottom:4 }}>{lbl}</div>
            <GradeBadge grade={grade} size='lg' />
          </div>
        ))}
      </div>
    </div>
  );

  return (
    <div>
      {/* Back + header */}
      <div style={{ display:'flex', alignItems:'center', gap:12, marginBottom:20 }}>
        <button onClick={onBack} style={{ background:'#EDE9FE', border:'none', borderRadius:10, padding:'8px 14px', cursor:'pointer', display:'flex', alignItems:'center', gap:6, fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, color:'#7C3AED' }}>
          <Icon name='chevL' size={16} color='#7C3AED' /> Back
        </button>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', display:'flex', gap:10, alignItems:'center' }}>
            {char.name} <RarityBadge rarity={char.rarity} />
          </div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{char.scenario} · Turn {char.turn}/{char.totalTurns} · {char.stage}</div>
        </div>
      </div>

      {/* Tabs */}
      <div style={{ display:'flex', gap:4, marginBottom:20, background:'#EDE9FE', borderRadius:12, padding:4, width:'fit-content' }}>
        {tabs.map(t=>(
          <button key={t} onClick={()=>setTab(t)} style={{ padding:'8px 18px', borderRadius:9, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, textTransform:'capitalize', background:tab===t?'#fff':'transparent', color:tab===t?'#7C3AED':'#7C6FAB', boxShadow:tab===t?'0 1px 6px rgba(124,58,237,0.15)':'' }}>{t}</button>
        ))}
      </div>

      {tab==='stats' && (
        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20 }}>
          <Card style={{ padding:24 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Stats Overview</div>
            {Object.entries(char.stats).map(([s,v])=><StatBar key={s} stat={s} value={v}/>)}
          </Card>
          <Card style={{ padding:24 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Condition</div>
            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:12, marginBottom:16 }}>
              {[
                { label:'Energy', val:`${char.energy}%`, color:'#7C3AED', icon:'⚡' },
                { label:'Mood', val:char.mood, color:'#E879A0', icon:'😊' },
                { label:'Condition', val:char.condition, color:'#10B981', icon:'💚' },
                { label:'SP Available', val:char.sp, color:'#F59E0B', icon:'✨' },
              ].map(item=>(
                <div key={item.label} style={{ background:'#F9F5FF', borderRadius:12, padding:'14px', border:'1px solid #EDE9FE' }}>
                  <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.icon} {item.label}</div>
                  <div style={{ fontSize:22, fontWeight:900, color:item.color, marginTop:4 }}>{item.val}</div>
                </div>
              ))}
            </div>
            <div style={{ background:'#F9F5FF', borderRadius:12, padding:'12px' }}>
              <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:6 }}>SP Budget</div>
              <div style={{ display:'flex', justifyContent:'space-between', fontSize:13, fontWeight:700, color:'#1E1033', marginBottom:6 }}>
                <span>Used: {char.usedSp}</span><span>Total: {char.totalSp}</span>
              </div>
              <div style={{ height:8, background:'#EDE9FE', borderRadius:99 }}>
                <div style={{ height:'100%', width:`${(char.usedSp/char.totalSp)*100}%`, background:'linear-gradient(90deg,#F59E0B,#F97316)', borderRadius:99 }}/>
              </div>
            </div>
          </Card>
        </div>
      )}

      {tab==='aptitudes' && (
        <Card style={{ padding:24 }}>
          <AptitudeGrid label="Distance" items={[
            { key:'sprint', label:'Sprint', grade:char.aptitudes.sprint },
            { key:'mile',   label:'Mile',   grade:char.aptitudes.mile },
            { key:'medium', label:'Medium', grade:char.aptitudes.medium },
            { key:'long',   label:'Long',   grade:char.aptitudes.long },
          ]}/>
          <AptitudeGrid label="Surface" items={[
            { key:'turf', label:'Turf', grade:char.aptitudes.turf },
            { key:'dirt', label:'Dirt', grade:char.aptitudes.dirt },
          ]}/>
          <AptitudeGrid label="Running Style" items={[
            { key:'frontRunner', label:'Front Runner 逃げ', grade:char.aptitudes.frontRunner },
            { key:'paceChaser',  label:'Pace Chaser 先行',  grade:char.aptitudes.paceChaser },
            { key:'lateSurger',  label:'Late Surger 差し',  grade:char.aptitudes.lateSurger },
            { key:'endCloser',   label:'End Closer 追込',   grade:char.aptitudes.endCloser },
          ]}/>
          <div style={{ background:'#F9F5FF', borderRadius:10, padding:'12px', fontSize:12, color:'#7C6FAB', marginTop:4 }}>
            <span style={{ color:'#F59E0B', fontWeight:800 }}>S</span> = +5% · <span style={{ color:'#E879A0', fontWeight:800 }}>A</span> = baseline · B = −10% · C = −20% · D = −30/40% · E = −50/60%
          </div>
        </Card>
      )}

      {tab==='factors' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Factor Inheritance</div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:12 }}>
            {char.factors.map((f,i)=>(
              <div key={i} style={{ background:'linear-gradient(135deg,#F9F5FF,#EDE9FE)', borderRadius:14, padding:'16px', textAlign:'center', border:'1px solid #C4B5FD' }}>
                <div style={{ fontSize:24, marginBottom:6 }}>{STAT_ICONS[f.stat]}</div>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:6 }}>{STAT_LABELS[f.stat]}</div>
                <div style={{ display:'flex', justifyContent:'center', gap:3, marginBottom:8 }}>
                  {[...Array(3)].map((_,j)=>(
                    <span key={j} style={{ fontSize:16, color:j<f.rating?'#F59E0B':'#D1D5DB' }}>★</span>
                  ))}
                </div>
                <div style={{ background:'#fff', borderRadius:8, padding:'4px 12px', fontSize:14, fontWeight:900, color:STAT_COLORS[f.stat] }}>+{f.bonus} {STAT_LABELS[f.stat]}</div>
              </div>
            ))}
          </div>
        </Card>
      )}

      {tab==='goals' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Goals</div>
          {char.goals.map(g=>{
            const statusMap = { on_track:{bg:'#D1FAE5',color:'#065F46',icon:'✅',label:'On Track'}, at_risk:{bg:'#FEE2E2',color:'#991B1B',icon:'🔴',label:'At Risk'}, completed:{bg:'#EDE9FE',color:'#5B21B6',icon:'✨',label:'Done'} };
            const s = statusMap[g.status] || statusMap.on_track;
            const pct = g.target===1 ? g.current*100 : Math.round((g.current/g.target)*100);
            return (
              <div key={g.id} style={{ marginBottom:16, padding:'16px', background:'#F9F5FF', borderRadius:12, border:'1px solid #EDE9FE' }}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:10 }}>
                  <span style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>{g.label}</span>
                  <span style={{ background:s.bg, color:s.color, fontSize:11, fontWeight:800, borderRadius:20, padding:'3px 10px' }}>{s.icon} {s.label}</span>
                </div>
                {g.target > 1 && <>
                  <div style={{ display:'flex', justifyContent:'space-between', fontSize:12, color:'#7C6FAB', marginBottom:6 }}><span>{g.current} / {g.target}</span><span>{pct}%</span></div>
                  <div style={{ height:8, background:'#EDE9FE', borderRadius:99 }}>
                    <div style={{ height:'100%', width:`${pct}%`, background:`linear-gradient(90deg,${g.status==='at_risk'?'#EF4444,#DC2626':'#E879A0,#7C3AED'})`, borderRadius:99 }}/>
                  </div>
                </>}
              </div>
            );
          })}
        </Card>
      )}

      {tab==='snapshots' && (
        <Card style={{ padding:24 }}>
          <Snapshots char={char} />
        </Card>
      )}

      {tab==='history' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Recent Turns</div>
          {char.recentTurns.map((t,i)=>(
            <div key={i} style={{ display:'flex', alignItems:'center', gap:14, padding:'12px 14px', background:'#F9F5FF', borderRadius:12, marginBottom:8, border:'1px solid #EDE9FE' }}>
              <div style={{ width:36, height:36, borderRadius:10, background:'linear-gradient(135deg,#E879A0,#7C3AED)', color:'#fff', display:'flex', alignItems:'center', justifyContent:'center', fontSize:12, fontWeight:800 }}>T{t.turn}</div>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:14, fontWeight:700, color:'#1E1033' }}>{t.action}</div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>{t.gains.join(' · ')}</div>
              </div>
              <div style={{ background:'#FEF3C7', color:'#92400E', borderRadius:8, padding:'3px 10px', fontSize:12, fontWeight:800 }}>+{t.sp} SP</div>
            </div>
          ))}
        </Card>
      )}
    </div>
  );
}

Object.assign(window, { Characters, CharacterDetail });
