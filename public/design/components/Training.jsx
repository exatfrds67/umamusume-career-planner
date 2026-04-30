
const FACILITY_MULTIPLIERS = [0, 1.0, 1.25, 1.5, 1.75, 2.0]; // index = level

// Soft-cap adjusted gain formula from SEQ-002
function calcActualGain(current, rawGain) {
  if (current >= 1200) return Math.min(Math.round(rawGain * 0.5), 50);
  if (current + rawGain > 1200) {
    const belowCap = 1200 - current;
    const aboveCap = Math.round((rawGain - belowCap) * 0.5);
    return Math.min(belowCap + aboveCap, 100);
  }
  return Math.min(rawGain, 100);
}

// Mood modifier from SEQ-002
const MOOD_MODS = { Great:0.04, Good:0.02, Normal:0, Bad:-0.02, Awful:-0.04 };

function Training() {
  const char = MOCK_CHARACTERS[0];
  const [selected, setSelected] = React.useState(null);
  const [result, setResult] = React.useState(null);
  const [aiEnabled, setAiEnabled] = React.useState(true);
  const [cacheHit] = React.useState(true);
  // Facility levels per training type (1-5)
  const [facilityLevels, setFacilityLevels] = React.useState({ speed:3, stamina:2, power:2, guts:2, wit:1 });
  const [showFacility, setShowFacility] = React.useState(false);

  const moodMod  = MOOD_MODS[char.mood] || 0;
  const riskColor = r => r<12?'#10B981':r<18?'#F59E0B':'#EF4444';
  const riskLabel = r => r<12?'Low':r<18?'Medium':'High';

  // Cards at ≥80% bond = Friendship Training active
  const friendshipCards = SUPPORT_DECK.filter(c => c.bond >= 80);
  const friendshipMult  = friendshipCards.length >= 3 ? 1.35 : friendshipCards.length >= 1 ? 1.10 : 1.0;

  // Compute adjusted gains for a training option
  const adjustedGains = (t) => {
    const lvl  = facilityLevels[t.id] || 1;
    const fMul = FACILITY_MULTIPLIERS[lvl];
    return Object.fromEntries(
      Object.entries(t.gains).map(([s, v]) => {
        const raw  = Math.round(v * fMul * (1 + moodMod) * friendshipMult);
        const actual = calcActualGain(char.stats[s] || 0, raw);
        return [s, actual];
      })
    );
  };

  const handleConfirm = () => {
    if (!selected) return;
    const gains = adjustedGains(selected);
    const failureRolled = Math.random() * 100 < selected.risk;
    setResult({
      training: selected,
      success: !failureRolled,
      gains: failureRolled ? {} : gains,
      spEarned: failureRolled ? 0 : selected.sp,
      fatigueAdded: selected.fatigue,
      energyAfter: Math.max(0, char.energy - selected.fatigue),
      moodAfter: char.energy - selected.fatigue < 30 ? 'Bad' : char.mood,
      skillHintTriggered: !failureRolled && selected.skills.length > 0 && Math.random() > 0.4,
      hintSkill: selected.skills[0],
      facilityLevel: facilityLevels[selected.id] || 1,
      friendshipActive: friendshipCards.length > 0,
    });
  };

  // ── Turn Result Modal ────────────────────────────────────────────────────
  const TurnResultModal = () => {
    if (!result) return null;
    return (
      <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.75)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
        <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:500, overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.3)' }}>
          <div style={{ background:result.success?'linear-gradient(135deg,#1E1033,#3B1F6E)':'linear-gradient(135deg,#7F1D1D,#3B1F6E)', padding:'20px 24px' }}>
            <div style={{ fontSize:32, marginBottom:6 }}>{result.success?'✅':'💥'}</div>
            <div style={{ fontSize:20, fontWeight:900, color:'#fff' }}>{result.success?`${result.training.label} Training Complete!`:'Training Failed!'}</div>
            <div style={{ display:'flex', gap:10, marginTop:8, flexWrap:'wrap' }}>
              <span style={{ fontSize:11, background:'rgba(255,255,255,0.15)', color:'#fff', borderRadius:6, padding:'2px 10px', fontWeight:700 }}>Facility Lv.{result.facilityLevel} ({FACILITY_MULTIPLIERS[result.facilityLevel]}×)</span>
              {result.friendshipActive && <span style={{ fontSize:11, background:'rgba(16,185,129,0.3)', color:'#6EE7B7', borderRadius:6, padding:'2px 10px', fontWeight:700 }}>🤝 Friendship Training Active</span>}
              <span style={{ fontSize:11, background:'rgba(255,255,255,0.1)', color:'rgba(255,255,255,0.7)', borderRadius:6, padding:'2px 10px' }}>Mood: {char.mood} ({moodMod>=0?'+':''}{Math.round(moodMod*100)}%)</span>
            </div>
          </div>
          <div style={{ padding:'24px' }}>
            {result.success ? (
              <>
                <div style={{ marginBottom:16 }}>
                  <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB', marginBottom:10 }}>STAT GAINS (Soft-cap adjusted)</div>
                  {Object.entries(result.gains).map(([s,v]) => {
                    const cur = char.stats[s] || 0;
                    const softCapWarning = cur > 1000;
                    return (
                      <div key={s} style={{ display:'flex', alignItems:'center', gap:10, marginBottom:8, padding:'8px 12px', background:'#F9F5FF', borderRadius:10 }}>
                        <span style={{ fontSize:16 }}>{STAT_ICONS[s]}</span>
                        <span style={{ fontSize:13, fontWeight:700, color:'#1E1033', flex:1 }}>{STAT_LABELS[s]}</span>
                        {softCapWarning && <span style={{ fontSize:10, color:'#F59E0B', fontWeight:800, background:'#FFFBEB', borderRadius:6, padding:'1px 6px' }}>Near cap</span>}
                        <span style={{ fontSize:12, color:'#7C6FAB' }}>{cur} → <strong style={{color:STAT_COLORS[s]}}>{cur+v}</strong></span>
                        <span style={{ fontSize:18, fontWeight:900, color:STAT_COLORS[s] }}>+{v}</span>
                      </div>
                    );
                  })}
                </div>
                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8, marginBottom:16 }}>
                  {[
                    { label:'SP Earned', val:`+${result.spEarned}`, icon:'✨', color:'#F59E0B', bg:'#FFFBEB' },
                    { label:'Energy After', val:`${result.energyAfter}%`, icon:'⚡', color:'#7C3AED', bg:'#F5F3FF' },
                    { label:'Mood After', val:result.moodAfter, icon:'😊', color:'#E879A0', bg:'#FDF2F8' },
                  ].map(item=>(
                    <div key={item.label} style={{ textAlign:'center', background:item.bg, borderRadius:12, padding:'10px 6px' }}>
                      <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.icon} {item.label}</div>
                      <div style={{ fontSize:16, fontWeight:900, color:item.color, marginTop:3 }}>{item.val}</div>
                    </div>
                  ))}
                </div>
                {result.skillHintTriggered && (
                  <div style={{ background:'linear-gradient(135deg,#D1FAE5,#A7F3D0)', borderRadius:12, padding:'12px 16px', marginBottom:16, border:'1px solid #6EE7B7', display:'flex', gap:10, alignItems:'center' }}>
                    <span style={{ fontSize:22 }}>💡</span>
                    <div>
                      <div style={{ fontSize:13, fontWeight:800, color:'#065F46' }}>Skill Hint Acquired!</div>
                      <div style={{ fontSize:13, color:'#047857' }}><strong>{result.hintSkill}</strong> — hint level increased</div>
                    </div>
                  </div>
                )}
              </>
            ) : (
              <div style={{ background:'#FEF2F2', borderRadius:12, padding:'16px', marginBottom:16, textAlign:'center' }}>
                <div style={{ fontSize:13, color:'#991B1B', fontWeight:700 }}>Training failed! No stat gains this turn. Energy still reduced by {result.fatigueAdded}.</div>
              </div>
            )}
            {result.energyAfter < 30 && <div style={{ background:'#FEF3C7', borderRadius:10, padding:'10px 14px', marginBottom:16, fontSize:12, color:'#92400E', fontWeight:700 }}>⚠️ Energy below 30% — consider resting next turn.</div>}
            <Btn variant='primary' style={{ width:'100%', justifyContent:'center' }} onClick={()=>setResult(null)}>Continue to Turn {char.turn+1} →</Btn>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div>
      {/* Header */}
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Training Selection</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{char.name} · Turn {char.turn} · {char.stage}</div>
        </div>
        <div style={{ display:'flex', gap:8, alignItems:'center', flexWrap:'wrap' }}>
          <div style={{ background:'#F0FDF4', borderRadius:10, padding:'7px 12px', fontSize:12, fontWeight:700, color:'#065F46', border:'1px solid #BBF7D0', display:'flex', gap:6, alignItems:'center' }}>
            <div style={{ width:7, height:7, borderRadius:99, background:'#10B981' }}/>{cacheHit?'Cached':'Fresh'}
          </div>
          <div onClick={()=>setShowFacility(!showFacility)} style={{ background:showFacility?'#F5F3FF':'#F9F5FF', borderRadius:10, padding:'7px 12px', fontSize:12, fontWeight:700, color:showFacility?'#7C3AED':'#7C6FAB', border:`1px solid ${showFacility?'#C4B5FD':'#EDE9FE'}`, cursor:'pointer' }}>
            🏋️ Facility Levels
          </div>
          <div onClick={()=>setAiEnabled(!aiEnabled)} style={{ background:aiEnabled?'#F5F3FF':'#F9F5FF', borderRadius:10, padding:'7px 12px', fontSize:12, fontWeight:700, color:aiEnabled?'#7C3AED':'#7C6FAB', border:`1px solid ${aiEnabled?'#C4B5FD':'#EDE9FE'}`, cursor:'pointer' }}>
            🤖 AI {aiEnabled?'ON':'OFF'}
          </div>
          <div style={{ background:'#FEF3C7', borderRadius:10, padding:'7px 12px', fontSize:12, fontWeight:800, color:'#92400E' }}>⚡ {char.energy}%</div>
          <MoodChip mood={char.mood} />
        </div>
      </div>

      {/* Facility levels panel */}
      {showFacility && (
        <Card style={{ padding:18, marginBottom:18 }}>
          <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14 }}>
            <div>
              <div style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>🏋️ Facility Levels</div>
              <div style={{ fontSize:12, color:'#7C6FAB' }}>Higher levels multiply base gains. Summer Camp auto-sets all to Lv.5.</div>
            </div>
            <Btn variant='secondary' size='sm' onClick={()=>setFacilityLevels({ speed:5, stamina:5, power:5, guts:5, wit:5 })}>☀️ Summer Camp (All Lv.5)</Btn>
          </div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:10 }}>
            {TRAINING_OPTIONS.map(t => {
              const lvl = facilityLevels[t.id] || 1;
              return (
                <div key={t.id} style={{ textAlign:'center' }}>
                  <div style={{ fontSize:18, marginBottom:4 }}>{t.icon}</div>
                  <div style={{ fontSize:11, fontWeight:800, color:t.color, marginBottom:6 }}>{t.label}</div>
                  <div style={{ display:'flex', gap:3, justifyContent:'center', marginBottom:6 }}>
                    {[1,2,3,4,5].map(l=>(
                      <button key={l} onClick={()=>setFacilityLevels(f=>({...f,[t.id]:l}))} style={{ width:24, height:24, borderRadius:6, border:`2px solid ${lvl>=l?t.color:'#EDE9FE'}`, background:lvl>=l?t.color+'22':'transparent', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:10, fontWeight:800, color:lvl>=l?t.color:'#D1D5DB' }}>{l}</button>
                    ))}
                  </div>
                  <div style={{ fontSize:12, fontWeight:900, color:t.color }}>{FACILITY_MULTIPLIERS[lvl]}×</div>
                  <div style={{ fontSize:10, color:'#7C6FAB' }}>+{Math.round((FACILITY_MULTIPLIERS[lvl]-1)*100)}% bonus</div>
                </div>
              );
            })}
          </div>
        </Card>
      )}

      {/* Friendship Training banner */}
      {friendshipCards.length > 0 && (
        <div style={{ background:'linear-gradient(135deg,#ECFDF5,#D1FAE5)', borderRadius:14, padding:'12px 18px', marginBottom:18, border:'1px solid #6EE7B7', display:'flex', gap:12, alignItems:'center' }}>
          <span style={{ fontSize:24 }}>🤝</span>
          <div style={{ flex:1 }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#065F46' }}>Friendship Training Active — {friendshipMult}× multiplier</div>
            <div style={{ fontSize:12, color:'#047857' }}>{friendshipCards.map(c=>c.name).join(', ')} at ≥80% bond · {friendshipCards.length >= 3 ? '3+ cards: 1.35×' : '1–2 cards: 1.10×'}</div>
          </div>
        </div>
      )}

      {/* AI tip */}
      {aiEnabled && (
        <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', borderRadius:16, padding:'16px 20px', marginBottom:20, display:'flex', alignItems:'center', gap:16 }}>
          <div style={{ width:40, height:40, borderRadius:12, background:'rgba(232,121,160,0.2)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:20, flexShrink:0 }}>🤖</div>
          <div style={{ flex:1 }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#F9A8D4', marginBottom:2 }}>AI Recommendation · TrainingAdvisorAgent</div>
            <div style={{ fontSize:13, color:'rgba(255,255,255,0.75)' }}>Speed training optimal · Lv.{facilityLevels.speed} facility ({FACILITY_MULTIPLIERS[facilityLevels.speed]}×) · {friendshipCards.length>0?`Friendship bonus active ·`:''} Low risk.</div>
          </div>
          <div style={{ background:'#E879A0', color:'#fff', borderRadius:8, padding:'4px 12px', fontSize:12, fontWeight:800, flexShrink:0 }}>PICK: SPEED</div>
        </div>
      )}

      <div style={{ display:'grid', gridTemplateColumns:selected?'1fr 380px':'1fr', gap:20 }}>
        <div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(200px,1fr))', gap:14 }}>
            {TRAINING_OPTIONS.map(t => {
              const sel  = selected?.id === t.id;
              const adj  = adjustedGains(t);
              const lvl  = facilityLevels[t.id] || 1;
              const hasFT = friendshipCards.some(c=>c.bondTraining===t.label||c.type===t.label);
              return (
                <div key={t.id} onClick={()=>setSelected(sel?null:t)} style={{ borderRadius:16, border:`2px solid ${sel?t.color:'#EDE9FE'}`, background:sel?t.bg:'#fff', padding:18, cursor:'pointer', transition:'all .15s', boxShadow:sel?`0 4px 20px ${t.color}33`:'none', position:'relative', overflow:'hidden' }}>
                  {t.recommended && <div style={{ position:'absolute', top:10, right:10, background:'#E879A0', color:'#fff', fontSize:9, fontWeight:900, borderRadius:20, padding:'2px 8px' }}>AI BEST</div>}
                  <div style={{ width:48, height:48, borderRadius:14, background:t.bg, display:'flex', alignItems:'center', justifyContent:'center', fontSize:24, marginBottom:10, border:`1px solid ${t.color}33` }}>{t.icon}</div>
                  <div style={{ display:'flex', gap:6, alignItems:'center', marginBottom:6 }}>
                    <span style={{ fontSize:16, fontWeight:900, color:'#1E1033' }}>{t.label}</span>
                    <span style={{ fontSize:10, background:t.color+'22', color:t.color, borderRadius:6, padding:'1px 6px', fontWeight:800 }}>Lv.{lvl} {FACILITY_MULTIPLIERS[lvl]}×</span>
                    {hasFT && <span style={{ fontSize:10, background:'#D1FAE5', color:'#065F46', borderRadius:6, padding:'1px 6px', fontWeight:800 }}>🤝FT</span>}
                  </div>
                  <div style={{ display:'flex', gap:5, marginBottom:10, flexWrap:'wrap' }}>
                    {Object.entries(adj).map(([s,v])=>(
                      <span key={s} style={{ background:STAT_COLORS[s]+'18', color:STAT_COLORS[s], borderRadius:8, padding:'3px 9px', fontSize:12, fontWeight:800 }}>+{v} {STAT_LABELS[s]}</span>
                    ))}
                  </div>
                  <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:6 }}>
                    <div style={{ textAlign:'center', background:'rgba(255,255,255,0.7)', borderRadius:8, padding:'5px 4px' }}>
                      <div style={{ fontSize:9, color:'#7C6FAB', fontWeight:700 }}>Risk</div>
                      <div style={{ fontSize:13, fontWeight:900, color:riskColor(t.risk) }}>{t.risk}%</div>
                    </div>
                    <div style={{ textAlign:'center', background:'rgba(255,255,255,0.7)', borderRadius:8, padding:'5px 4px' }}>
                      <div style={{ fontSize:9, color:'#7C6FAB', fontWeight:700 }}>Fatigue</div>
                      <div style={{ fontSize:13, fontWeight:900, color:'#7C6FAB' }}>{t.fatigue}</div>
                    </div>
                    <div style={{ textAlign:'center', background:'rgba(255,255,255,0.7)', borderRadius:8, padding:'5px 4px' }}>
                      <div style={{ fontSize:9, color:'#7C6FAB', fontWeight:700 }}>SP</div>
                      <div style={{ fontSize:13, fontWeight:900, color:'#F59E0B' }}>+{t.sp}</div>
                    </div>
                  </div>
                  {t.bonusNote && <div style={{ marginTop:8, fontSize:10, color:'#7C3AED', fontWeight:700, background:'#EDE9FE', borderRadius:8, padding:'3px 8px' }}>🔗 {t.bonusNote}</div>}
                  {t.skills.length>0 && <div style={{ marginTop:6, fontSize:10, color:'#10B981', fontWeight:700 }}>💡 Hint: {t.skills.join(', ')}</div>}
                </div>
              );
            })}
          </div>
        </div>

        {/* Detail panel */}
        {selected && (() => {
          const adj = adjustedGains(selected);
          const lvl = facilityLevels[selected.id] || 1;
          return (
            <Card style={{ padding:24, alignSelf:'start', position:'sticky', top:0 }}>
              <div style={{ display:'flex', alignItems:'center', gap:12, marginBottom:16 }}>
                <div style={{ width:52, height:52, borderRadius:14, background:selected.bg, display:'flex', alignItems:'center', justifyContent:'center', fontSize:28 }}>{selected.icon}</div>
                <div>
                  <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>{selected.label} Training</div>
                  <div style={{ display:'flex', gap:6, marginTop:3 }}>
                    <span style={{ fontSize:11, background:selected.color+'22', color:selected.color, borderRadius:6, padding:'2px 8px', fontWeight:800 }}>Lv.{lvl} — {FACILITY_MULTIPLIERS[lvl]}×</span>
                    {friendshipCards.length>0 && <span style={{ fontSize:11, background:'#D1FAE5', color:'#065F46', borderRadius:6, padding:'2px 8px', fontWeight:800 }}>🤝 {friendshipMult}×</span>}
                  </div>
                </div>
              </div>

              {/* Formula breakdown */}
              <div style={{ background:'#F9F5FF', borderRadius:12, padding:'12px', marginBottom:14, fontSize:11, color:'#7C6FAB', lineHeight:1.8 }}>
                <div style={{ fontWeight:800, color:'#7C3AED', marginBottom:4 }}>Gain Formula</div>
                Base × {FACILITY_MULTIPLIERS[lvl]}× (facility) × {(1+moodMod).toFixed(2)} (mood) × {friendshipMult} (FT) → soft-cap adjusted
              </div>

              <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:10 }}>Predicted Gains</div>
              {Object.entries(adj).map(([s,v])=>{
                const cur = char.stats[s] || 0;
                const after = cur + v;
                const atRisk = cur > 1000;
                return (
                  <div key={s} style={{ marginBottom:10, padding:'10px 12px', background:'#F9F5FF', borderRadius:12, border:atRisk?'1px solid #FCD34D':'1px solid transparent' }}>
                    <div style={{ display:'flex', justifyContent:'space-between', marginBottom:4 }}>
                      <span style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>{STAT_ICONS[s]} {STAT_LABELS[s]}</span>
                      <div style={{ display:'flex', gap:6, alignItems:'center' }}>
                        {atRisk && <span style={{ fontSize:9, background:'#FEF3C7', color:'#92400E', borderRadius:6, padding:'1px 6px', fontWeight:800 }}>Soft-cap zone</span>}
                        <span style={{ fontSize:16, fontWeight:900, color:STAT_COLORS[s] }}>+{v}</span>
                      </div>
                    </div>
                    <div style={{ fontSize:12, color:'#7C6FAB' }}>{cur} → <strong style={{color:STAT_COLORS[s]}}>{after}</strong> <GradeBadge grade={getGrade(after)}/></div>
                    <div style={{ height:5, background:'#EDE9FE', borderRadius:99, marginTop:6, position:'relative' }}>
                      <div style={{ position:'absolute', left:0, height:'100%', width:`${(cur/1200)*100}%`, background:STAT_COLORS[s]+'55', borderRadius:99 }}/>
                      <div style={{ position:'absolute', left:`${(cur/1200)*100}%`, height:'100%', width:`${(v/1200)*100}%`, background:STAT_COLORS[s], borderRadius:99 }}/>
                      <div style={{ position:'absolute', left:`${(1000/1200)*100}%`, height:'100%', width:1, background:'#F59E0B' }}/>
                      <div style={{ position:'absolute', left:`${(1200/1200)*100 - .1}%`, height:'100%', width:1, background:'#EF4444' }}/>
                    </div>
                  </div>
                );
              })}

              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8, marginBottom:14 }}>
                {[
                  { label:'Risk', val:`${selected.risk}%`, color:riskColor(selected.risk) },
                  { label:'Fatigue', val:selected.fatigue, color:'#7C6FAB' },
                  { label:'SP Earn', val:`+${selected.sp}`, color:'#F59E0B' },
                ].map(item=>(
                  <div key={item.label} style={{ textAlign:'center', background:'#F9F5FF', borderRadius:10, padding:'10px 6px' }}>
                    <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>{item.label}</div>
                    <div style={{ fontSize:18, fontWeight:900, color:item.color }}>{item.val}</div>
                  </div>
                ))}
              </div>

              {selected.skills.length>0 && <div style={{ marginBottom:14, padding:'10px 12px', background:'#F0FDF4', borderRadius:12, border:'1px solid #BBF7D0' }}><div style={{ fontSize:12, fontWeight:800, color:'#065F46', marginBottom:2 }}>💡 Skill Hint Chance</div><div style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>{selected.skills.join(', ')}</div></div>}
              {selected.bonusNote && <div style={{ marginBottom:14, padding:'10px 12px', background:'#EDE9FE', borderRadius:12 }}><div style={{ fontSize:12, fontWeight:800, color:'#7C3AED' }}>🔗 Bond Bonus</div><div style={{ fontSize:13, color:'#1E1033', marginTop:2 }}>{selected.bonusNote}</div></div>}
              <Btn variant='primary' style={{ width:'100%', justifyContent:'center' }} onClick={handleConfirm}>Confirm {selected.label} Training</Btn>
            </Card>
          );
        })()}
      </div>

      <TurnResultModal />
    </div>
  );
}

Object.assign(window, { Training });
