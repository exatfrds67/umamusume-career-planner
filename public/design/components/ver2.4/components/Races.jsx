
function Races() {
  const [selected, setSelected] = React.useState(UPCOMING_RACES[0]);
  const [tab, setTab] = React.useState('upcoming');
  const [raceEntry, setRaceEntry] = React.useState(null); // {race, step: 'enter'|'result'}
  const [placement, setPlacement] = React.useState(1);
  const char = MOCK_CHARACTERS[0];

  const readinessColor = r => r>=80?'#10B981':r>=60?'#F59E0B':'#EF4444';
  const readinessLabel = r => r>=80?'Excellent':r>=70?'Good':r>=55?'Fair':'Poor';

  const statCheck = (stat, value) => {
    const t = { speed:{ok:500,good:700}, stamina:{ok:450,good:650}, power:{ok:420,good:620}, guts:{ok:440,good:640}, wit:{ok:430,good:630} }[stat];
    if (value >= t.good) return { icon:'✅', label:'Strong',    color:'#10B981' };
    if (value >= t.ok)   return { icon:'⚠️', label:'Adequate', color:'#F59E0B' };
    return                      { icon:'❌', label:'Weak',      color:'#EF4444' };
  };

  // Reward calc based on placement + grade
  const calcRewards = (race, place) => {
    const gradeBase = { G1:{ fans:3200, sp:180 }, G2:{ fans:1800, sp:120 }, G3:{ fans:900, sp:80 } }[race.grade] || { fans:400, sp:50 };
    const mult = [1.0, 0.65, 0.4, 0.2, 0.1, 0.05, 0.02, 0.01][place-1] || 0.01;
    return { fans: Math.round(gradeBase.fans * mult), sp: place <= 3 ? Math.round(gradeBase.sp * mult) : 0, statBonus: place === 1 ? 8 : place <= 3 ? 4 : 0 };
  };

  const RACE_HISTORY = [
    { name:'Junior Mile Cup',    grade:'G3', place:1, turn:24, winProb:62, fans:900,  sp:80  },
    { name:'Autumn Mile Stakes', grade:'G2', place:2, turn:35, winProb:45, fans:1170, sp:78  },
    { name:'Spring Sprint',      grade:'G3', place:1, turn:28, winProb:58, fans:900,  sp:80  },
  ];

  // ── Race Entry Modal ─────────────────────────────────────────────────────
  const RaceEntryModal = () => {
    if (!raceEntry) return null;
    const race = raceEntry.race;
    const rewards = calcRewards(race, placement);
    const placeSuffix = ['st','nd','rd','th','th','th','th','th'][placement-1];
    const isSuccess = placement <= 3;

    // Check objectives
    const objectivesMet = char.goals
      .filter(g => g.type === 'race' || race.grade === 'G1')
      .map(g => ({ label: g.label, met: placement === 1 }));

    return (
      <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.8)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
        <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:520, overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.3)', maxHeight:'90vh', overflowY:'auto' }}>

          {raceEntry.step === 'enter' && (
            <>
              <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', padding:'20px 24px' }}>
                <div style={{ fontSize:13, color:'rgba(255,255,255,0.5)', marginBottom:4 }}>🏁 Entering Race</div>
                <div style={{ fontSize:20, fontWeight:900, color:'#fff' }}>{race.name}</div>
                <div style={{ fontSize:13, color:'rgba(255,255,255,0.5)' }}>{race.grade} · {race.distance} · Turn {race.turn}</div>
              </div>
              <div style={{ padding:24 }}>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:12 }}>Record Your Placement</div>
                <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:8, marginBottom:20 }}>
                  {[1,2,3,4,5,6,7,8].map(p=>(
                    <button key={p} onClick={()=>setPlacement(p)} style={{
                      padding:'12px 6px', borderRadius:12, border:`2px solid ${placement===p?'#E879A0':'#EDE9FE'}`,
                      background: placement===p?'linear-gradient(135deg,#FDF2F8,#F5F3FF)':'#F9F5FF',
                      cursor:'pointer', fontFamily:'Nunito,sans-serif', fontWeight:900,
                      fontSize:16, color:placement===p?'#E879A0':'#7C6FAB',
                      textAlign:'center',
                    }}>{p}<span style={{ fontSize:10 }}>{['st','nd','rd','th','th','th','th','th'][p-1]}</span></button>
                  ))}
                </div>

                {/* Rewards preview */}
                <div style={{ background:'#F9F5FF', borderRadius:14, padding:'14px', marginBottom:16 }}>
                  <div style={{ fontSize:12, fontWeight:800, color:'#7C6FAB', marginBottom:10 }}>REWARD PREVIEW</div>
                  <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8 }}>
                    {[
                      { label:'Fans', val:`+${rewards.fans.toLocaleString()}`, icon:'👥', color:'#E879A0' },
                      { label:'Skill Pts', val: rewards.sp > 0 ? `+${rewards.sp}` : '—', icon:'✨', color:'#F59E0B' },
                      { label:'Stat Bonus', val: rewards.statBonus > 0 ? `+${rewards.statBonus}` : '—', icon:'📈', color:'#10B981' },
                    ].map(item=>(
                      <div key={item.label} style={{ textAlign:'center', background:'#fff', borderRadius:10, padding:'10px 6px', border:'1px solid #EDE9FE' }}>
                        <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.icon} {item.label}</div>
                        <div style={{ fontSize:18, fontWeight:900, color:item.color, marginTop:3 }}>{item.val}</div>
                      </div>
                    ))}
                  </div>
                </div>

                <div style={{ display:'flex', gap:10 }}>
                  <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={()=>setRaceEntry({...raceEntry, step:'result'})}>
                    Record Result
                  </Btn>
                  <Btn variant='ghost' onClick={()=>setRaceEntry(null)}>Cancel</Btn>
                </div>
              </div>
            </>
          )}

          {raceEntry.step === 'result' && (
            <>
              <div style={{ background: isSuccess?'linear-gradient(135deg,#064E3B,#065F46)':'linear-gradient(135deg,#7F1D1D,#991B1B)', padding:'20px 24px' }}>
                <div style={{ fontSize:40, marginBottom:8 }}>{placement===1?'🏆':placement<=3?'🎖️':'😔'}</div>
                <div style={{ fontSize:22, fontWeight:900, color:'#fff' }}>
                  {placement}{placeSuffix} Place — {placement===1?'Victory!':placement<=3?'Podium Finish':'Not Placed'}
                </div>
                <div style={{ fontSize:13, color:'rgba(255,255,255,0.6)', marginTop:4 }}>{race.name} · {race.grade}</div>
              </div>
              <div style={{ padding:24 }}>
                {/* Rewards */}
                <div style={{ marginBottom:16 }}>
                  <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB', marginBottom:10 }}>REWARDS EARNED</div>
                  <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8 }}>
                    {[
                      { label:'Fans Gained', val:`+${rewards.fans.toLocaleString()}`, icon:'👥', color:'#E879A0', bg:'#FDF2F8' },
                      { label:'SP Earned', val:rewards.sp>0?`+${rewards.sp}`:'—', icon:'✨', color:'#F59E0B', bg:'#FFFBEB' },
                      { label:'Stat Bonus', val:rewards.statBonus>0?`+${rewards.statBonus} all`:'—', icon:'📈', color:'#10B981', bg:'#F0FDF4' },
                    ].map(item=>(
                      <div key={item.label} style={{ textAlign:'center', background:item.bg, borderRadius:12, padding:'12px 6px', border:`1px solid ${item.color}33` }}>
                        <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.icon} {item.label}</div>
                        <div style={{ fontSize:18, fontWeight:900, color:item.color, marginTop:3 }}>{item.val}</div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Objective checks */}
                {objectivesMet.length > 0 && (
                  <div style={{ marginBottom:16 }}>
                    <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB', marginBottom:8 }}>SCENARIO OBJECTIVES</div>
                    {objectivesMet.map((o,i)=>(
                      <div key={i} style={{ display:'flex', gap:8, alignItems:'center', padding:'8px 12px', background:o.met?'#D1FAE5':'#FEE2E2', borderRadius:10, marginBottom:6 }}>
                        <span>{o.met?'✅':'❌'}</span>
                        <span style={{ fontSize:13, fontWeight:700, color:o.met?'#065F46':'#991B1B' }}>{o.label}</span>
                        <span style={{ marginLeft:'auto', fontSize:11, fontWeight:800, color:o.met?'#10B981':'#EF4444' }}>{o.met?'Completed':'Missed'}</span>
                      </div>
                    ))}
                  </div>
                )}

                {/* AI recovery tip if bad result */}
                {!isSuccess && (
                  <div style={{ background:'linear-gradient(135deg,#F5F3FF,#EDE9FE)', borderRadius:12, padding:'12px 16px', marginBottom:16, border:'1px solid #C4B5FD' }}>
                    <div style={{ fontSize:12, fontWeight:800, color:'#7C3AED', marginBottom:6 }}>🤖 AI Recovery Advice</div>
                    <div style={{ fontSize:13, color:'#1E1033' }}>Focus on 3× Speed training before the next G-grade race. Your readiness was below the recommended 80% threshold.</div>
                  </div>
                )}

                <Btn variant={isSuccess?'gold':'primary'} style={{ width:'100%', justifyContent:'center' }} onClick={()=>setRaceEntry(null)}>
                  {isSuccess ? '🎉 Back to Dashboard' : 'Continue Training'}
                </Btn>
              </div>
            </>
          )}
        </div>
      </div>
    );
  };

  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>Race Strategy</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>{char.name} · {char.stage} · Turn {char.turn}</div>

      <div style={{ display:'flex', gap:4, marginBottom:20, background:'#EDE9FE', borderRadius:12, padding:4, width:'fit-content' }}>
        {['upcoming','history'].map(t=>(
          <button key={t} onClick={()=>setTab(t)} style={{ padding:'8px 20px', borderRadius:9, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, textTransform:'capitalize', background:tab===t?'#fff':'transparent', color:tab===t?'#7C3AED':'#7C6FAB', boxShadow:tab===t?'0 1px 6px rgba(124,58,237,0.15)':'' }}>{t}</button>
        ))}
      </div>

      {tab==='upcoming' && (
        <div style={{ display:'grid', gridTemplateColumns:'340px 1fr', gap:20 }}>
          <div style={{ display:'flex', flexDirection:'column', gap:12 }}>
            {UPCOMING_RACES.map(r => {
              const rc = readinessColor(r.readiness);
              const sel = selected?.id===r.id;
              return (
                <div key={r.id} onClick={()=>setSelected(r)} style={{ padding:'16px', borderRadius:14, border:`2px solid ${sel?'#7C3AED':'#EDE9FE'}`, background:sel?'linear-gradient(135deg,#F5F3FF,#EDE9FE)':'#fff', cursor:'pointer', transition:'all .15s' }}>
                  <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:8 }}>
                    <div>
                      <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>{r.name}</div>
                      <div style={{ fontSize:12, color:'#7C6FAB', marginTop:2 }}>{r.distance} · {r.surface} · Turn {r.turn}</div>
                    </div>
                    <div style={{ display:'flex', flexDirection:'column', gap:4, alignItems:'flex-end' }}>
                      <span style={{ background:r.grade==='G1'?'linear-gradient(135deg,#F59E0B,#F97316)':r.grade==='G2'?'#EDE9FE':'#F3F4F6', color:r.grade==='G1'?'#fff':'#7C3AED', borderRadius:6, padding:'2px 10px', fontSize:12, fontWeight:800 }}>{r.grade}</span>
                      {r.required && <span style={{ fontSize:10, background:'#FEE2E2', color:'#991B1B', borderRadius:6, padding:'2px 8px', fontWeight:800 }}>Required</span>}
                    </div>
                  </div>
                  <div style={{ display:'flex', alignItems:'center', gap:8 }}>
                    <div style={{ flex:1, height:6, background:'#EDE9FE', borderRadius:99 }}>
                      <div style={{ height:'100%', width:`${r.readiness}%`, background:rc, borderRadius:99 }}/>
                    </div>
                    <span style={{ fontSize:13, fontWeight:900, color:rc, minWidth:36 }}>{r.readiness}%</span>
                    <span style={{ fontSize:11, color:'#7C6FAB' }}>{readinessLabel(r.readiness)}</span>
                  </div>
                </div>
              );
            })}
          </div>

          {selected && (
            <Card style={{ padding:24 }}>
              <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
                <div>
                  <div style={{ fontSize:20, fontWeight:900, color:'#1E1033' }}>{selected.name}</div>
                  <div style={{ display:'flex', gap:8, marginTop:6, flexWrap:'wrap', alignItems:'center' }}>
                    <span style={{ background:selected.grade==='G1'?'linear-gradient(135deg,#F59E0B,#F97316)':'#EDE9FE', color:selected.grade==='G1'?'#fff':'#7C3AED', borderRadius:6, padding:'3px 12px', fontSize:13, fontWeight:800 }}>{selected.grade}</span>
                    <span style={{ fontSize:13, color:'#7C6FAB' }}>{selected.distance} · {selected.surface} · Turn {selected.turn}</span>
                    {selected.required && <span style={{ background:'#FEE2E2', color:'#991B1B', borderRadius:6, padding:'2px 10px', fontSize:12, fontWeight:800 }}>Required</span>}
                  </div>
                </div>
                <div style={{ textAlign:'center' }}>
                  <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700, marginBottom:2 }}>READINESS</div>
                  <div style={{ fontSize:40, fontWeight:900, color:readinessColor(selected.readiness) }}>{selected.readiness}%</div>
                  <div style={{ fontSize:12, fontWeight:800, color:readinessColor(selected.readiness) }}>{readinessLabel(selected.readiness)}</div>
                </div>
              </div>

              <div style={{ marginBottom:20 }}>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:12 }}>Stat Requirements</div>
                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8 }}>
                  {Object.entries(char.stats).map(([s,v])=>{
                    const chk = statCheck(s, v);
                    return (
                      <div key={s} style={{ display:'flex', alignItems:'center', gap:8, padding:'10px 12px', background:'#F9F5FF', borderRadius:10 }}>
                        <span style={{ fontSize:16 }}>{STAT_ICONS[s]}</span>
                        <div style={{ flex:1 }}>
                          <div style={{ fontSize:12, fontWeight:700, color:'#1E1033' }}>{STAT_LABELS[s]}</div>
                          <div style={{ fontSize:13, fontWeight:900, color:STAT_COLORS[s] }}>{v}</div>
                        </div>
                        <span style={{ fontSize:12, fontWeight:800, color:chk.color }}>{chk.icon} {chk.label}</span>
                      </div>
                    );
                  })}
                </div>
              </div>

              <div style={{ marginBottom:20, padding:'14px', background:'linear-gradient(135deg,#F5F3FF,#EDE9FE)', borderRadius:12, border:'1px solid #C4B5FD' }}>
                <div style={{ fontSize:13, fontWeight:800, color:'#7C3AED', marginBottom:4 }}>Recommended Style · RaceStrategyAgent</div>
                <div style={{ fontSize:20, fontWeight:900, color:'#1E1033' }}>{selected.style}</div>
                <div style={{ fontSize:12, color:'#7C6FAB', marginTop:4 }}>Best fit for your aptitudes and current stat spread</div>
              </div>

              <div style={{ marginBottom:20 }}>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:10 }}>Performance Forecast</div>
                {[
                  { label:'1st Place', pct:selected.winProb, color:'#F59E0B' },
                  { label:'2nd Place', pct:Math.round(selected.winProb*1.1), color:'#C4B5FD' },
                  { label:'3rd Place', pct:Math.round(selected.winProb*0.5), color:'#D1D5DB' },
                  { label:'4th+', pct:Math.max(0,100-selected.winProb-Math.round(selected.winProb*1.1)-Math.round(selected.winProb*0.5)), color:'#F3F4F6' },
                ].map(item=>(
                  <div key={item.label} style={{ display:'flex', alignItems:'center', gap:10, marginBottom:6 }}>
                    <span style={{ fontSize:12, fontWeight:700, color:'#7C6FAB', width:70 }}>{item.label}</span>
                    <div style={{ flex:1, height:16, background:'#F3F4F6', borderRadius:99, overflow:'hidden' }}>
                      <div style={{ height:'100%', width:`${item.pct}%`, background:item.color, borderRadius:99, display:'flex', alignItems:'center', paddingLeft:8 }}>
                        {item.pct>15&&<span style={{ fontSize:10, fontWeight:800, color:item.label==='4th+'?'#6B7280':'#fff' }}>{item.pct}%</span>}
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              <div style={{ display:'flex', gap:10 }}>
                <Btn variant='primary' style={{ flex:1, justifyContent:'center' }} onClick={()=>{ setPlacement(1); setRaceEntry({race:selected,step:'enter'}); }}>Enter Race</Btn>
                <Btn variant='secondary'>Run Simulation</Btn>
              </div>
            </Card>
          )}
        </div>
      )}

      {tab==='history' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Race History</div>
          {RACE_HISTORY.map((r,i)=>(
            <div key={i} style={{ display:'flex', alignItems:'center', gap:14, padding:'14px', background:r.place===1?'#FFFBEB':'#F9F5FF', borderRadius:12, marginBottom:8, border:`1px solid ${r.place===1?'#FCD34D':'#EDE9FE'}` }}>
              <div style={{ width:44, height:44, borderRadius:12, background:r.place===1?'linear-gradient(135deg,#F59E0B,#F97316)':r.place===2?'linear-gradient(135deg,#C4B5FD,#7C3AED)':'#EDE9FE', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16, fontWeight:900, color:r.place<=2?'#fff':'#7C6FAB', flexShrink:0 }}>
                {r.place}{['st','nd','rd','th'][r.place-1]||'th'}
              </div>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>{r.name}</div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>{r.grade} · Turn {r.turn}</div>
              </div>
              <div style={{ display:'flex', gap:10, alignItems:'center' }}>
                <span style={{ fontSize:12, color:'#E879A0', fontWeight:800 }}>👥 +{r.fans.toLocaleString()}</span>
                <span style={{ fontSize:12, color:'#F59E0B', fontWeight:800 }}>✨ +{r.sp} SP</span>
              </div>
            </div>
          ))}
        </Card>
      )}

      <RaceEntryModal />
    </div>
  );
}

Object.assign(window, { Races });
