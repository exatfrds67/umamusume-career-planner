
function Dashboard({ setScreen }) {
  const char = MOCK_CHARACTERS[0];
  const stats = char.stats;

  const goalStatusStyle = s => ({
    on_track:{ bg:'#D1FAE5', color:'#065F46', label:'On Track', icon:'✅' },
    at_risk:{ bg:'#FEE2E2', color:'#991B1B', label:'At Risk', icon:'🔴' },
    completed:{ bg:'#EDE9FE', color:'#5B21B6', label:'Done', icon:'✨' },
  }[s] || { bg:'#F3F4F6', color:'#374151', label:'Unknown', icon:'❓' });

  const turnPct = Math.round((char.turn / char.totalTurns) * 100);

  return (
    <div>
      {/* Welcome banner */}
      <div style={{ background:'linear-gradient(135deg,#1E1033 0%,#3B1F6E 50%,#4C1060 100%)', borderRadius:20, padding:'24px 28px', marginBottom:24, display:'flex', alignItems:'center', justifyContent:'space-between', overflow:'hidden', position:'relative' }}>
        <div style={{ position:'absolute', right:-20, top:-20, width:200, height:200, borderRadius:99, background:'rgba(232,121,160,0.12)', pointerEvents:'none' }}/>
        <div style={{ position:'absolute', right:80, bottom:-40, width:140, height:140, borderRadius:99, background:'rgba(124,58,237,0.1)', pointerEvents:'none' }}/>
        <div style={{ position:'relative' }}>
          <div style={{ fontSize:13, color:'rgba(255,255,255,0.5)', fontWeight:600, marginBottom:4 }}>🏇 Active Career Run</div>
          <div style={{ fontSize:24, fontWeight:900, color:'#fff', marginBottom:4 }}>{char.name}</div>
          <div style={{ display:'flex', gap:10, flexWrap:'wrap' }}>
            <RarityBadge rarity={char.rarity} />
            <span style={{ background:'rgba(255,255,255,0.1)', color:'rgba(255,255,255,0.8)', borderRadius:6, padding:'2px 10px', fontSize:12, fontWeight:700 }}>{char.scenario}</span>
            <MoodChip mood={char.mood} />
          </div>
        </div>
        <div style={{ textAlign:'right', position:'relative' }}>
          <div style={{ fontSize:11, color:'rgba(255,255,255,0.45)', fontWeight:700, marginBottom:4 }}>CAREER PROGRESS</div>
          <div style={{ fontSize:36, fontWeight:900, color:'#F9A8D4', lineHeight:1 }}>Turn {char.turn}</div>
          <div style={{ fontSize:12, color:'rgba(255,255,255,0.5)', marginBottom:8 }}>of {char.totalTurns} · {char.stage}</div>
          <div style={{ width:160, height:6, background:'rgba(255,255,255,0.1)', borderRadius:99 }}>
            <div style={{ height:'100%', width:`${turnPct}%`, background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99 }}/>
          </div>
          <div style={{ fontSize:11, color:'rgba(255,255,255,0.4)', marginTop:4 }}>{turnPct}% complete</div>
        </div>
      </div>

      {/* 2-col grid */}
      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20, marginBottom:20 }}>
        {/* Stats snapshot */}
        <Card style={{ padding:20 }}>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>Stats Snapshot</div>
            <button onClick={()=>setScreen('characters')} style={{ fontSize:12, color:'#7C3AED', fontWeight:700, background:'none', border:'none', cursor:'pointer' }}>View Detail →</button>
          </div>
          {Object.entries(stats).map(([s,v])=><StatBar key={s} stat={s} value={v}/>)}
          <div style={{ marginTop:10, padding:'10px 12px', background:'#F9F5FF', borderRadius:10, fontSize:12, color:'#7C6FAB', fontWeight:600 }}>
            <span style={{ color:'#7C3AED', fontWeight:800 }}>Soft cap</span> at 1200 · stats above 1000 show diminishing returns
          </div>
        </Card>

        {/* Goals + Energy */}
        <div style={{ display:'flex', flexDirection:'column', gap:16 }}>
          <Card style={{ padding:20 }}>
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
              <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>Active Goals</div>
              <button style={{ fontSize:12, color:'#E879A0', fontWeight:700, background:'none', border:'none', cursor:'pointer' }}>+ Add</button>
            </div>
            {char.goals.map(g=>{
              const s = goalStatusStyle(g.status);
              const pct = g.target===1 ? (g.current*100) : Math.round((g.current/g.target)*100);
              return (
                <div key={g.id} style={{ marginBottom:12 }}>
                  <div style={{ display:'flex', justifyContent:'space-between', marginBottom:4 }}>
                    <span style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>{g.label}</span>
                    <span style={{ background:s.bg, color:s.color, fontSize:10, fontWeight:800, borderRadius:20, padding:'2px 8px' }}>{s.icon} {s.label}</span>
                  </div>
                  {g.target > 1 && (
                    <div>
                      <div style={{ display:'flex', justifyContent:'space-between', fontSize:11, color:'#7C6FAB', marginBottom:2 }}>
                        <span>{g.current} / {g.target}</span><span>{pct}%</span>
                      </div>
                      <div style={{ height:6, background:'#EDE9FE', borderRadius:99 }}>
                        <div style={{ height:'100%', width:`${pct}%`, background:`linear-gradient(90deg,${g.status==='at_risk'?'#EF4444':'#E879A0'},${g.status==='at_risk'?'#DC2626':'#7C3AED'})`, borderRadius:99 }}/>
                      </div>
                    </div>
                  )}
                </div>
              );
            })}
          </Card>

          {/* Mood + Energy card */}
          <Card style={{ padding:20 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Condition</div>
            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:10 }}>
              {[
                { label:'Energy', value:char.energy, color:'#7C3AED', icon:'⚡', suffix:'%' },
                { label:'Mood', value:char.mood, color:'#E879A0', icon:'😊', suffix:'' },
              ].map(item=>(
                <div key={item.label} style={{ background:'#F9F5FF', borderRadius:12, padding:'12px' }}>
                  <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.icon} {item.label}</div>
                  <div style={{ fontSize:20, fontWeight:900, color:item.color, marginTop:4 }}>{item.value}{item.suffix}</div>
                </div>
              ))}
            </div>
            <div style={{ marginTop:10, fontSize:12, color:'#7C6FAB', textAlign:'center', background:'#F9F5FF', borderRadius:8, padding:'6px' }}>Condition: <span style={{ fontWeight:800, color:'#10B981' }}>Normal</span> · Good for training</div>
          </Card>
        </div>
      </div>

      {/* Bottom 3-col: Training suggestions, Upcoming races, AI tip */}
      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:20 }}>
        {/* Training suggestions */}
        <Card style={{ padding:20 }}>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>Training Suggestions</div>
            <button onClick={()=>setScreen('training')} style={{ fontSize:12, color:'#7C3AED', fontWeight:700, background:'none', border:'none', cursor:'pointer' }}>All →</button>
          </div>
          {TRAINING_OPTIONS.slice(0,3).map(t=>(
            <div key={t.id} onClick={()=>setScreen('training')} style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 12px', borderRadius:10, marginBottom:6, cursor:'pointer', background:t.recommended?'linear-gradient(90deg,#FDF2F8,#F5F3FF)':'#F9F5FF', border:t.recommended?'1px solid #F9A8D4':'1px solid #EDE9FE' }}>
              <div style={{ width:36, height:36, borderRadius:10, background:t.bg, display:'flex', alignItems:'center', justifyContent:'center', fontSize:18 }}>{t.icon}</div>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', display:'flex', gap:6, alignItems:'center' }}>
                  {t.label}
                  {t.recommended && <span style={{ fontSize:9, background:'#E879A0', color:'#fff', borderRadius:20, padding:'1px 6px', fontWeight:900 }}>BEST</span>}
                </div>
                <div style={{ fontSize:11, color:'#7C6FAB' }}>+{Object.values(t.gains)[0]} · {t.sp} SP</div>
              </div>
              <div style={{ fontSize:12, fontWeight:800, color:t.risk<12?'#10B981':t.risk<18?'#F59E0B':'#EF4444' }}>{t.risk}% risk</div>
            </div>
          ))}
        </Card>

        {/* Upcoming races */}
        <Card style={{ padding:20 }}>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>Upcoming Races</div>
            <button onClick={()=>setScreen('races')} style={{ fontSize:12, color:'#7C3AED', fontWeight:700, background:'none', border:'none', cursor:'pointer' }}>All →</button>
          </div>
          {UPCOMING_RACES.map(r=>{
            const rc = r.readiness>=80?'#10B981':r.readiness>=60?'#F59E0B':'#EF4444';
            return (
              <div key={r.id} onClick={()=>setScreen('races')} style={{ padding:'10px 12px', borderRadius:10, marginBottom:6, cursor:'pointer', background:'#F9F5FF', border:'1px solid #EDE9FE' }}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                  <span style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{r.name}</span>
                  <span style={{ fontSize:11, fontWeight:800, background:r.grade==='G1'?'linear-gradient(135deg,#F59E0B,#F97316)':'#EDE9FE', color:r.grade==='G1'?'#fff':'#7C3AED', borderRadius:6, padding:'2px 8px' }}>{r.grade}</span>
                </div>
                <div style={{ fontSize:11, color:'#7C6FAB', marginTop:3 }}>{r.distance} · Turn {r.turn} {r.required&&<span style={{color:'#EF4444',fontWeight:700}}>· Required</span>}</div>
                <div style={{ display:'flex', alignItems:'center', gap:8, marginTop:6 }}>
                  <div style={{ flex:1, height:5, background:'#EDE9FE', borderRadius:99 }}>
                    <div style={{ height:'100%', width:`${r.readiness}%`, background:rc, borderRadius:99 }}/>
                  </div>
                  <span style={{ fontSize:11, fontWeight:800, color:rc }}>{r.readiness}%</span>
                </div>
              </div>
            );
          })}
        </Card>

        {/* AI advisor card */}
        <Card style={{ padding:20, background:'linear-gradient(135deg,#F9F5FF,#EDE9FE)', border:'1px solid #C4B5FD' }}>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', display:'flex', gap:8, alignItems:'center' }}>
              <span style={{ fontSize:18 }}>🤖</span> AI Advisor
            </div>
            <button onClick={()=>setScreen('ai-advisor')} style={{ fontSize:12, color:'#7C3AED', fontWeight:700, background:'none', border:'none', cursor:'pointer' }}>Chat →</button>
          </div>
          <div style={{ background:'#fff', borderRadius:12, padding:'12px', marginBottom:12, border:'1px solid #EDE9FE', fontSize:13, color:'#1E1033', lineHeight:1.6 }}>
            <span style={{ fontWeight:800, color:'#7C3AED' }}>Recommendation:</span> Focus Speed training this turn. With 78% energy and Good mood, you'll get ~52 Speed + skill hint chance.
          </div>
          <div style={{ display:'flex', gap:6 }}>
            <Btn variant='primary' size='sm' onClick={()=>setScreen('training')}>Train Now</Btn>
            <Btn variant='secondary' size='sm' onClick={()=>setScreen('ai-advisor')}>Ask More</Btn>
          </div>
          <div style={{ marginTop:12, fontSize:11, color:'#7C6FAB', display:'flex', gap:6 }}>
            <span>Powered by</span>
            <span style={{ fontWeight:800, color:'#7C3AED' }}>AWS Bedrock Claude</span>
          </div>
        </Card>
      </div>
    </div>
  );
}

Object.assign(window, { Dashboard });
