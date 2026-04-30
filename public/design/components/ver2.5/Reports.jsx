
// ── Career Reports ────────────────────────────────────────────────────────────
function CareerReports() {
  const char = MOCK_CHARACTERS[0];
  const [tab, setTab] = React.useState('summary');
  const [cacheHit] = React.useState(true);
  const [exporting, setExporting] = React.useState(null);

  // Simulated progression data over turns
  const TURN_DATA = [
    { turn:1,  speed:180, stamina:160, power:150, guts:160, wit:155, sp:0   },
    { turn:6,  speed:220, stamina:195, power:180, guts:190, wit:185, sp:45  },
    { turn:12, speed:270, stamina:240, power:220, guts:235, wit:230, sp:120 },
    { turn:18, speed:320, stamina:280, power:260, guts:280, wit:270, sp:210 },
    { turn:24, speed:380, stamina:330, power:305, guts:325, wit:315, sp:320 },
    { turn:30, speed:430, stamina:375, power:350, guts:365, wit:360, sp:420 },
    { turn:36, speed:470, stamina:415, power:390, guts:405, wit:400, sp:510 },
    { turn:42, speed:505, stamina:453, power:425, guts:440, wit:435, sp:590 },
    { turn:45, speed:520, stamina:480, power:440, guts:460, wit:450, sp:450 },
  ];

  const TRAINING_LOG = [
    { turn:44, type:'Speed',   gains:{ speed:48, power:14 }, sp:15, mood:'Good',   risk:12, success:true  },
    { turn:43, type:'Stamina', gains:{ stamina:42, guts:10 }, sp:12, mood:'Good',  risk:8,  success:true  },
    { turn:42, type:'Power',   gains:{ power:35, speed:12 }, sp:14, mood:'Normal', risk:18, success:true  },
    { turn:41, type:'Guts',    gains:{ guts:40, stamina:8 }, sp:10, mood:'Good',   risk:10, success:true  },
    { turn:40, type:'Speed',   gains:{ speed:52, power:15 }, sp:15, mood:'Great',  risk:12, success:true  },
    { turn:39, type:'Wit',     gains:{ wit:38, guts:6 },     sp:12, mood:'Good',   risk:6,  success:false },
    { turn:38, type:'Speed',   gains:{ speed:0, power:0 },   sp:0,  mood:'Bad',    risk:14, success:false },
    { turn:37, type:'Stamina', gains:{ stamina:44, guts:9 }, sp:12, mood:'Normal', risk:8,  success:true  },
  ];

  const RACE_LOG = [
    { turn:24, name:'Junior Mile Cup',    grade:'G3', place:1, winProb:62, fans:900  },
    { turn:28, name:'Spring Sprint',      grade:'G3', place:1, winProb:58, fans:900  },
    { turn:35, name:'Autumn Mile Stakes', grade:'G2', place:2, winProb:45, fans:1170 },
    { turn:40, name:'Classic Derby',      grade:'G2', place:3, winProb:38, fans:720  },
  ];

  const totalStatGain = Object.entries(char.stats).reduce((s,[k,v])=>{
    const start = TURN_DATA[0][k] || 0;
    return s + (v - start);
  }, 0);
  const trainSuccessRate = Math.round(TRAINING_LOG.filter(t=>t.success).length/TRAINING_LOG.length*100);
  const raceWinRate = Math.round(RACE_LOG.filter(r=>r.place===1).length/RACE_LOG.length*100);
  const totalSPEarned = TRAINING_LOG.reduce((s,t)=>s+t.sp,0) + RACE_LOG.reduce((s,r)=>s+(r.place<=3?80:0),0);

  const handleExport = (fmt) => {
    setExporting(fmt);
    setTimeout(()=>setExporting(null), 2000);
  };

  // Mini SVG line chart
  const LineChart = ({ data, keys, colors, width=400, height=120 }) => {
    const pad = { t:10, r:10, b:20, l:36 };
    const W = width - pad.l - pad.r;
    const H = height - pad.t - pad.b;
    const allVals = data.flatMap(d => keys.map(k=>d[k]||0));
    const minV = 0, maxV = Math.max(...allVals, 1);
    const x = i => pad.l + (i/(data.length-1))*W;
    const y = v => pad.t + H - ((v-minV)/(maxV-minV))*H;
    return (
      <svg viewBox={`0 0 ${width} ${height}`} style={{ width:'100%', height:'auto' }}>
        {/* Grid lines */}
        {[0,.25,.5,.75,1].map((f,i)=>(
          <g key={i}>
            <line x1={pad.l} x2={pad.l+W} y1={pad.t+H*(1-f)} y2={pad.t+H*(1-f)} stroke="#EDE9FE" strokeWidth={.8}/>
            <text x={pad.l-4} y={pad.t+H*(1-f)+4} fontSize={8} fill="#9CA3AF" textAnchor="end">{Math.round(minV+(maxV-minV)*f)}</text>
          </g>
        ))}
        {/* Turn labels */}
        {data.map((d,i)=>(
          <text key={i} x={x(i)} y={height-4} fontSize={8} fill="#9CA3AF" textAnchor="middle">T{d.turn}</text>
        ))}
        {/* Lines */}
        {keys.map((k,ki)=>{
          const pts = data.map((d,i)=>`${x(i)},${y(d[k]||0)}`).join(' ');
          return (
            <g key={k}>
              <polyline points={pts} fill="none" stroke={colors[ki]} strokeWidth={2} strokeLinejoin="round"/>
              {data.map((d,i)=>(
                <circle key={i} cx={x(i)} cy={y(d[k]||0)} r={3} fill={colors[ki]} stroke="#fff" strokeWidth={1.5}/>
              ))}
            </g>
          );
        })}
      </svg>
    );
  };

  // Bar chart for training distribution
  const BarChart = ({ data, width=400, height=100 }) => {
    const pad = { t:8, r:8, b:20, l:8 };
    const W = width - pad.l - pad.r;
    const H = height - pad.t - pad.b;
    const maxV = Math.max(...data.map(d=>d.value));
    const bw = W/data.length - 6;
    return (
      <svg viewBox={`0 0 ${width} ${height}`} style={{ width:'100%', height:'auto' }}>
        {data.map((d,i)=>{
          const bh = (d.value/maxV)*H;
          const bx = pad.l + i*(W/data.length) + 3;
          const by = pad.t + H - bh;
          return (
            <g key={i}>
              <rect x={bx} y={by} width={bw} height={bh} rx={3} fill={d.color} opacity={.85}/>
              <text x={bx+bw/2} y={by-3} fontSize={8} fill={d.color} textAnchor="middle" fontWeight="bold">{d.value}</text>
              <text x={bx+bw/2} y={height-4} fontSize={8} fill="#9CA3AF" textAnchor="middle">{d.label}</text>
            </g>
          );
        })}
      </svg>
    );
  };

  const trainingDist = Object.entries(
    TRAINING_LOG.reduce((acc,t)=>{ acc[t.type]=(acc[t.type]||0)+1; return acc; }, {})
  ).map(([label,value])=>({ label, value, color:STAT_COLORS[label.toLowerCase()]||'#7C3AED' }));

  return (
    <div>
      {/* Header */}
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Career Report</div>
          <div style={{ fontSize:13, color:'#7C6FAB', display:'flex', gap:8, alignItems:'center' }}>
            {char.name} · Turn {char.turn} · {char.stage}
            <span style={{ background:cacheHit?'#D1FAE5':'#FEF3C7', color:cacheHit?'#065F46':'#92400E', borderRadius:6, padding:'1px 8px', fontSize:11, fontWeight:700 }}>{cacheHit?'⚡ Cached':'🔄 Fresh'}</span>
          </div>
        </div>
        <div style={{ display:'flex', gap:8 }}>
          {['json','csv','pdf'].map(fmt=>(
            <Btn key={fmt} variant={exporting===fmt?'secondary':'ghost'} size='sm' onClick={()=>handleExport(fmt)}>
              {exporting===fmt?'✓ Exported!`':`⬇ ${fmt.toUpperCase()}`}
            </Btn>
          ))}
        </div>
      </div>

      {/* Summary cards */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:14, marginBottom:24 }}>
        {[
          { label:'Total Stat Gain', val:`+${totalStatGain}`, icon:'📈', color:'#7C3AED', bg:'#F5F3FF' },
          { label:'Training Success', val:`${trainSuccessRate}%`, icon:'🏋️', color:'#10B981', bg:'#F0FDF4' },
          { label:'Race Win Rate', val:`${raceWinRate}%`, icon:'🏆', color:'#F59E0B', bg:'#FFFBEB' },
          { label:'SP Earned Total', val:totalSPEarned, icon:'✨', color:'#E879A0', bg:'#FDF2F8' },
        ].map(item=>(
          <Card key={item.label} style={{ padding:18, background:item.bg, border:`1px solid ${item.color}22` }}>
            <div style={{ fontSize:11, fontWeight:700, color:item.color, marginBottom:4 }}>{item.icon} {item.label}</div>
            <div style={{ fontSize:28, fontWeight:900, color:item.color }}>{item.val}</div>
          </Card>
        ))}
      </div>

      {/* Tabs */}
      <div style={{ display:'flex', gap:4, marginBottom:20, background:'#EDE9FE', borderRadius:12, padding:4, width:'fit-content' }}>
        {['summary','training','races','export'].map(t=>(
          <button key={t} onClick={()=>setTab(t)} style={{ padding:'8px 18px', borderRadius:9, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, textTransform:'capitalize', background:tab===t?'#fff':'transparent', color:tab===t?'#7C3AED':'#7C6FAB', boxShadow:tab===t?'0 1px 6px rgba(124,58,237,0.15)':'' }}>{t}</button>
        ))}
      </div>

      {tab==='summary' && (
        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20 }}>
          {/* Stat progression chart */}
          <Card style={{ padding:22 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:6 }}>Stat Progression</div>
            <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:14 }}>All stats across {char.turn} turns · CareerReportingService</div>
            <LineChart
              data={TURN_DATA}
              keys={['speed','stamina','power','guts','wit']}
              colors={[STAT_COLORS.speed,STAT_COLORS.stamina,STAT_COLORS.power,STAT_COLORS.guts,STAT_COLORS.wit]}
            />
            <div style={{ display:'flex', gap:10, marginTop:10, flexWrap:'wrap' }}>
              {Object.entries(STAT_COLORS).map(([s,c])=>(
                <span key={s} style={{ display:'flex', gap:4, alignItems:'center', fontSize:11, fontWeight:700, color:c }}>
                  <span style={{ width:16, height:3, background:c, borderRadius:2, display:'inline-block' }}/>{STAT_LABELS[s]}
                </span>
              ))}
            </div>
          </Card>

          {/* Training distribution */}
          <Card style={{ padding:22 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:6 }}>Training Distribution</div>
            <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:14 }}>Sessions by type · last {TRAINING_LOG.length} turns</div>
            <BarChart data={trainingDist} />
            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8, marginTop:14 }}>
              {[
                { label:'Success Rate', val:`${trainSuccessRate}%`, color:'#10B981' },
                { label:'Failure Rate', val:`${100-trainSuccessRate}%`, color:'#EF4444' },
                { label:'Avg SP/Turn', val:Math.round(totalSPEarned/TRAINING_LOG.length), color:'#F59E0B' },
                { label:'Total Sessions', val:TRAINING_LOG.length, color:'#7C3AED' },
              ].map(item=>(
                <div key={item.label} style={{ background:'#F9F5FF', borderRadius:10, padding:'10px 12px' }}>
                  <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.label}</div>
                  <div style={{ fontSize:18, fontWeight:900, color:item.color }}>{item.val}</div>
                </div>
              ))}
            </div>
          </Card>

          {/* Race performance */}
          <Card style={{ padding:22 }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Race Performance</div>
            {RACE_LOG.map((r,i)=>(
              <div key={i} style={{ display:'flex', gap:12, alignItems:'center', padding:'10px 12px', background:r.place===1?'#FFFBEB':'#F9F5FF', borderRadius:10, marginBottom:8, border:`1px solid ${r.place===1?'#FCD34D':'#EDE9FE'}` }}>
                <div style={{ width:36, height:36, borderRadius:10, background:r.place===1?'linear-gradient(135deg,#F59E0B,#F97316)':r.place===2?'linear-gradient(135deg,#C4B5FD,#7C3AED)':'#EDE9FE', display:'flex', alignItems:'center', justifyContent:'center', fontSize:13, fontWeight:900, color:r.place<=2?'#fff':'#7C6FAB', flexShrink:0 }}>
                  {r.place}{['st','nd','rd','th'][r.place-1]||'th'}
                </div>
                <div style={{ flex:1 }}>
                  <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{r.name}</div>
                  <div style={{ fontSize:11, color:'#7C6FAB' }}>{r.grade} · Turn {r.turn} · Predicted {r.winProb}%</div>
                </div>
                <span style={{ fontSize:12, fontWeight:800, color:'#E879A0' }}>+{r.fans.toLocaleString()} fans</span>
              </div>
            ))}
          </Card>

          {/* AI Insights */}
          <Card style={{ padding:22, background:'linear-gradient(135deg,#F5F3FF,#EDE9FE)', border:'1px solid #C4B5FD' }}>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:14 }}>🤖 AI Insights · CareerAnalyticsService</div>
            {[
              { icon:'📈', text:'Speed training efficiency: 94% — strong performer. Consider pushing to Lv.4 facility.' },
              { icon:'⚠️', text:'Stamina 6.3% below ideal pace for Classic Year. Recommend 2 dedicated turns.' },
              { icon:'🎯', text:'On track for URA Finals with projected Speed 780+ by Turn 60.' },
              { icon:'💡', text:'Wit underinvested at 450. Skill activation rate could improve with 2–3 Wit sessions.' },
            ].map((ins,i)=>(
              <div key={i} style={{ display:'flex', gap:10, padding:'10px 12px', background:'#fff', borderRadius:10, marginBottom:8, border:'1px solid #EDE9FE' }}>
                <span style={{ fontSize:16 }}>{ins.icon}</span>
                <span style={{ fontSize:13, color:'#1E1033', lineHeight:1.5 }}>{ins.text}</span>
              </div>
            ))}
          </Card>
        </div>
      )}

      {tab==='training' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Training Session Log · career.trainingSessions</div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:8, padding:'10px 14px', background:'#F9F5FF', borderRadius:10, marginBottom:12, fontSize:11, fontWeight:800, color:'#7C6FAB' }}>
            <span>Turn</span><span>Type</span><span>Gains</span><span>SP</span><span>Result</span>
          </div>
          {TRAINING_LOG.map((t,i)=>(
            <div key={i} style={{ display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:8, padding:'10px 14px', background:t.success?'#fff':'#FEF2F2', borderRadius:10, marginBottom:6, border:`1px solid ${t.success?'#EDE9FE':'#FECACA'}`, alignItems:'center' }}>
              <span style={{ fontSize:13, fontWeight:800, color:'#7C3AED' }}>T{t.turn}</span>
              <span style={{ fontSize:13, fontWeight:700, color:STAT_COLORS[t.type.toLowerCase()]||'#1E1033' }}>{STAT_ICONS[t.type.toLowerCase()]||''} {t.type}</span>
              <span style={{ fontSize:12, color:'#7C6FAB' }}>{t.success?Object.entries(t.gains).map(([s,v])=>`+${v} ${s}`).join(', '):'—'}</span>
              <span style={{ fontSize:13, fontWeight:800, color:'#F59E0B' }}>{t.success?`+${t.sp}`:'—'}</span>
              <span style={{ fontSize:12, fontWeight:800, color:t.success?'#10B981':'#EF4444' }}>{t.success?'✅ Success':'💥 Failed'}</span>
            </div>
          ))}
        </Card>
      )}

      {tab==='races' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Race History · career.races</div>
          {RACE_LOG.map((r,i)=>(
            <div key={i} style={{ display:'flex', gap:14, alignItems:'center', padding:'14px', background:r.place===1?'#FFFBEB':'#F9F5FF', borderRadius:12, marginBottom:10, border:`1px solid ${r.place===1?'#FCD34D':'#EDE9FE'}` }}>
              <div style={{ width:48, height:48, borderRadius:12, background:r.place===1?'linear-gradient(135deg,#F59E0B,#F97316)':r.place===2?'linear-gradient(135deg,#C4B5FD,#7C3AED)':'#EDE9FE', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16, fontWeight:900, color:r.place<=2?'#fff':'#7C6FAB', flexShrink:0 }}>
                {r.place}{['st','nd','rd','th'][r.place-1]||'th'}
              </div>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>{r.name}</div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>{r.grade} · Turn {r.turn}</div>
              </div>
              <div style={{ display:'flex', gap:16, textAlign:'right' }}>
                <div>
                  <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>WIN PROB</div>
                  <div style={{ fontSize:15, fontWeight:900, color:'#7C3AED' }}>{r.winProb}%</div>
                </div>
                <div>
                  <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>FANS</div>
                  <div style={{ fontSize:15, fontWeight:900, color:'#E879A0' }}>+{r.fans.toLocaleString()}</div>
                </div>
              </div>
            </div>
          ))}
          <div style={{ padding:'14px', background:'#F5F3FF', borderRadius:12, border:'1px solid #EDE9FE', marginTop:8 }}>
            <div style={{ display:'flex', gap:20 }}>
              {[
                { label:'Races Run', val:RACE_LOG.length },
                { label:'Wins', val:RACE_LOG.filter(r=>r.place===1).length },
                { label:'Podiums', val:RACE_LOG.filter(r=>r.place<=3).length },
                { label:'Total Fans', val:RACE_LOG.reduce((s,r)=>s+r.fans,0).toLocaleString() },
              ].map(item=>(
                <div key={item.label} style={{ textAlign:'center' }}>
                  <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>{item.label}</div>
                  <div style={{ fontSize:20, fontWeight:900, color:'#7C3AED' }}>{item.val}</div>
                </div>
              ))}
            </div>
          </div>
        </Card>
      )}

      {tab==='export' && (
        <Card style={{ padding:24 }}>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Export Career Data · CareerReportingService</div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:14 }}>
            {[
              { fmt:'JSON', icon:'{ }', desc:'Full structured career payload including all training sessions, race outcomes, skill acquisitions, and stat history. Owner-scoped.', route:'/reports/career/{career}/export/json', color:'#7C3AED', bg:'#F5F3FF' },
              { fmt:'CSV', icon:'⊞', desc:'Tabular export of training sessions and race history. Compatible with Excel, Google Sheets, and analytics tools.', route:'/reports/career/{career}/export/csv', color:'#10B981', bg:'#F0FDF4' },
              { fmt:'PDF', icon:'📄', desc:'Print-optimized career report view. Triggers browser print-to-PDF dialog. No server-side PDF generation.', route:'/reports/career/{career}/export/pdf', color:'#E879A0', bg:'#FDF2F8' },
            ].map(item=>(
              <div key={item.fmt} style={{ background:item.bg, borderRadius:14, padding:20, border:`1px solid ${item.color}22` }}>
                <div style={{ fontSize:36, marginBottom:10 }}>{item.icon}</div>
                <div style={{ fontSize:16, fontWeight:900, color:'#1E1033', marginBottom:6 }}>{item.fmt} Export</div>
                <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:12, lineHeight:1.5 }}>{item.desc}</div>
                <div style={{ fontSize:10, fontFamily:'monospace', color:item.color, background:'#fff', borderRadius:6, padding:'4px 8px', marginBottom:12, wordBreak:'break-all' }}>{item.route}</div>
                <Btn variant={exporting===item.fmt.toLowerCase()?'secondary':'primary'} size='sm' onClick={()=>handleExport(item.fmt.toLowerCase())}>
                  {exporting===item.fmt.toLowerCase()?'✓ Exported!':'⬇ Export '+item.fmt}
                </Btn>
              </div>
            ))}
          </div>
          <div style={{ marginTop:16, padding:'12px 16px', background:'#FEF3C7', borderRadius:10, fontSize:12, color:'#92400E', fontWeight:700 }}>
            ⚠️ All exports are owner-scoped · Cache invalidates on training session / race outcome / skill acquisition changes
          </div>
        </Card>
      )}
    </div>
  );
}

// ── Target Race Planning ──────────────────────────────────────────────────────
function TargetRacePlanning() {
  const char = MOCK_CHARACTERS[0];
  const [targets, setTargets] = React.useState([
    { id:2, name:'Kanto Okami Cup', grade:'G1', distance:'Medium (2400m)', surface:'Turf', turn:55, readiness:72, winProb:35, style:'Late Surger', required:true  },
    { id:3, name:'Spring Tenno Sho', grade:'G1', distance:'Long (3200m)',   surface:'Turf', turn:60, readiness:52, winProb:18, style:'Pace Chaser', required:false },
  ]);
  const [catalog, setCatalog] = React.useState([
    { id:4, name:'Autumn Tenno Sho', grade:'G1', distance:'Long (3200m)',   surface:'Turf', turn:64, readiness:null, winProb:null, style:'Pace Chaser' },
    { id:5, name:'Champions Cup',    grade:'G1', distance:'Medium (2000m)', surface:'Dirt', turn:68, readiness:null, winProb:null, style:'Front Runner' },
    { id:6, name:'Victoria Mile',    grade:'G1', distance:'Mile (1600m)',   surface:'Turf', turn:57, readiness:null, winProb:null, style:'Late Surger' },
    { id:7, name:'Takarazuka Kinen', grade:'G1', distance:'Medium (2200m)', surface:'Turf', turn:62, readiness:null, winProb:null, style:'Pace Chaser' },
    { id:8, name:'Mainichi Cup',     grade:'G2', distance:'Mile (1600m)',   surface:'Turf', turn:52, readiness:null, winProb:null, style:'Late Surger' },
    { id:9, name:'October Stakes',   grade:'G2', distance:'Medium (2000m)', surface:'Turf', turn:58, readiness:null, winProb:null, style:'Pace Chaser' },
  ]);
  const [refreshing, setRefreshing] = React.useState(null);
  const [replacing, setReplacing] = React.useState(null);

  const readinessColor = r => r>=80?'#10B981':r>=60?'#F59E0B':'#EF4444';

  const handleRefresh = (id) => {
    setRefreshing(id);
    setTimeout(()=>{
      setTargets(t=>t.map(r=>r.id===id?{...r, readiness:Math.min(100,r.readiness+Math.round(Math.random()*8)), winProb:Math.min(99,r.winProb+Math.round(Math.random()*5))}:r));
      setRefreshing(null);
    },1200);
  };

  const handleAdd = (race) => {
    if (targets.length >= 4) return;
    setTargets(t=>[...t,{...race, readiness:Math.round(40+Math.random()*40), winProb:Math.round(15+Math.random()*35)}]);
  };

  const handleRemove = (id) => setTargets(t=>t.filter(r=>r.id!==id));

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Target Race Planning</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{char.name} · /races/targets · RaceController::targets()</div>
        </div>
        <div style={{ background:'#F9F5FF', borderRadius:10, padding:'8px 14px', fontSize:12, fontWeight:700, color:'#7C6FAB', border:'1px solid #EDE9FE' }}>
          {targets.length}/4 targets selected
        </div>
      </div>

      {/* Target slots */}
      <div style={{ marginBottom:28 }}>
        <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:14 }}>🎯 Selected Targets</div>
        {targets.length === 0 && (
          <div style={{ textAlign:'center', padding:'40px', background:'#F9F5FF', borderRadius:14, border:'2px dashed #C4B5FD', color:'#7C6FAB', fontSize:14, fontWeight:700 }}>
            No target races selected — add from the catalog below
          </div>
        )}
        <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(280px,1fr))', gap:16 }}>
          {targets.map(r=>{
            const rc = readinessColor(r.readiness);
            return (
              <Card key={r.id} style={{ padding:20, border:`2px solid ${rc}44` }}>
                <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:12 }}>
                  <div>
                    <div style={{ fontSize:15, fontWeight:900, color:'#1E1033' }}>{r.name}</div>
                    <div style={{ fontSize:12, color:'#7C6FAB', marginTop:2 }}>{r.distance} · {r.surface} · Turn {r.turn}</div>
                  </div>
                  <div style={{ display:'flex', gap:6 }}>
                    <span style={{ background:r.grade==='G1'?'linear-gradient(135deg,#F59E0B,#F97316)':'#EDE9FE', color:r.grade==='G1'?'#fff':'#7C3AED', borderRadius:6, padding:'2px 8px', fontSize:11, fontWeight:800 }}>{r.grade}</span>
                    {r.required && <span style={{ background:'#FEE2E2', color:'#991B1B', borderRadius:6, padding:'2px 8px', fontSize:10, fontWeight:800 }}>Required</span>}
                  </div>
                </div>

                {/* Readiness */}
                <div style={{ marginBottom:10 }}>
                  <div style={{ display:'flex', justifyContent:'space-between', fontSize:12, marginBottom:4 }}>
                    <span style={{ color:'#7C6FAB', fontWeight:700 }}>Readiness</span>
                    <span style={{ fontWeight:900, color:rc }}>{r.readiness}%</span>
                  </div>
                  <div style={{ height:7, background:'#EDE9FE', borderRadius:99 }}>
                    <div style={{ height:'100%', width:`${r.readiness}%`, background:rc, borderRadius:99, transition:'width .4s' }}/>
                  </div>
                </div>

                {/* Win prob + style */}
                <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8, marginBottom:12 }}>
                  <div style={{ background:'#F9F5FF', borderRadius:8, padding:'8px', textAlign:'center' }}>
                    <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>Win Prob</div>
                    <div style={{ fontSize:18, fontWeight:900, color:'#7C3AED' }}>{r.winProb}%</div>
                  </div>
                  <div style={{ background:'#F9F5FF', borderRadius:8, padding:'8px', textAlign:'center' }}>
                    <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>Style</div>
                    <div style={{ fontSize:11, fontWeight:800, color:'#1E1033' }}>{r.style}</div>
                  </div>
                </div>

                <div style={{ display:'flex', gap:8 }}>
                  <Btn variant='secondary' size='sm' onClick={()=>handleRefresh(r.id)} style={{ flex:1, justifyContent:'center' }}>
                    {refreshing===r.id ? '⏳ Refreshing…' : '🔄 Refresh Readiness'}
                  </Btn>
                  <Btn variant='danger' size='sm' onClick={()=>handleRemove(r.id)}>✕</Btn>
                </div>
              </Card>
            );
          })}
        </div>
      </div>

      {/* Race catalog */}
      <div>
        <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:6 }}>GameRace Catalog · /races/calendar</div>
        <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:14 }}>Add races to your target list · AdvisoryController provides readiness on add</div>
        <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(260px,1fr))', gap:12 }}>
          {catalog.filter(c=>!targets.find(t=>t.id===c.id)).map(race=>(
            <div key={race.id} style={{ padding:'14px 16px', background:'#fff', borderRadius:12, border:'1px solid #EDE9FE', display:'flex', alignItems:'center', gap:12 }}>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{race.name}</div>
                <div style={{ fontSize:11, color:'#7C6FAB', marginTop:2 }}>{race.grade} · {race.distance} · Turn {race.turn}</div>
                <div style={{ fontSize:11, color:'#7C6FAB' }}>{race.surface} · {race.style}</div>
              </div>
              <Btn variant={targets.length>=4?'ghost':'primary'} size='sm' disabled={targets.length>=4} onClick={()=>handleAdd(race)}>+ Add</Btn>
            </div>
          ))}
        </div>
        {targets.length >= 4 && (
          <div style={{ marginTop:12, padding:'10px 14px', background:'#FEF3C7', borderRadius:10, fontSize:12, color:'#92400E', fontWeight:700 }}>
            ⚠️ Maximum 4 target races reached. Remove one to add another.
          </div>
        )}
      </div>
    </div>
  );
}

Object.assign(window, { CareerReports, TargetRacePlanning });
