
function App() {
  const [screen, setScreen] = React.useState(
    () => localStorage.getItem('uma_screen') || 'dashboard'
  );
  const [wizardOpen, setWizardOpen] = React.useState(false);
  const [wizardDone, setWizardDone] = React.useState(false);

  const navigate = (s) => {
    setScreen(s);
    localStorage.setItem('uma_screen', s);
  };

  const openWizard  = () => setWizardOpen(true);
  const closeWizard = () => setWizardOpen(false);
  const completeWizard = () => { setWizardOpen(false); setWizardDone(true); setTimeout(()=>setWizardDone(false),3000); navigate('characters'); };

  const screenProps = { setScreen: navigate };

  const renderScreen = () => {
    switch(screen) {
      case 'dashboard':     return <Dashboard {...screenProps} />;
      case 'characters':    return <Characters {...screenProps} openWizard={openWizard} />;
      case 'training':      return <Training {...screenProps} />;
      case 'races':         return <Races {...screenProps} />;
      case 'skills':        return <Skills {...screenProps} />;
      case 'support-cards': return <SupportCards {...screenProps} />;
      case 'ai-advisor':    return <AIAdvisor {...screenProps} />;
      case 'achievements':  return <Achievements />;
      case 'mcp':           return <MCPMonitor />;
      case 'performance':   return <Performance />;
      case 'admin':         return <AdminPanel />;
      case 'ocr':           return <OCRUpload />;
      case 'data':          return <DataManagement />;
      case 'settings':      return <Settings />;
      default:              return <Dashboard {...screenProps} />;
    }
  };

  return (
    <>
      <Layout screen={screen} setScreen={navigate}>
        {renderScreen()}
      </Layout>

      {wizardOpen && (
        <CharacterWizard onClose={closeWizard} onComplete={completeWizard} />
      )}

      {wizardDone && (
        <div style={{ position:'fixed', bottom:32, right:32, background:'linear-gradient(135deg,#10B981,#059669)', color:'#fff', borderRadius:16, padding:'16px 24px', fontFamily:'Nunito,sans-serif', fontWeight:800, fontSize:15, zIndex:200, boxShadow:'0 8px 32px rgba(16,185,129,0.4)', display:'flex', gap:10, alignItems:'center' }}>
          🎉 Character created! Ready to start training.
        </div>
      )}
    </>
  );
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
