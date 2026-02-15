import React, { useState, useEffect } from 'react';
import { Mail, Lock, Eye, EyeOff, Check, UserPlus, LogIn, GraduationCap, Info } from 'lucide-react';

export default function PortalAcademico() {
  const [email, setEmail] = useState('admin@admin.com');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });

  // Efecto de paralaje sutil en el fondo basado en el movimiento del mouse
  useEffect(() => {
    const handleMouseMove = (e) => {
      setMousePosition({
        x: (e.clientX / window.innerWidth) * 20,
        y: (e.clientY / window.innerHeight) * 20,
      });
    };
    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsLoading(true);
    // Simulación de carga
    setTimeout(() => setIsLoading(false), 2000);
  };

  return (
    <div className="login-container">
      
      {/* --- Fondo Dinámico --- */}
      <div className="background-wrapper">
        <div 
          className="orb orb-1"
          style={{ transform: `translate(${mousePosition.x * -1}px, ${mousePosition.y * -1}px)` }}
        ></div>
        <div 
          className="orb orb-2"
          style={{ transform: `translate(${mousePosition.x}px, ${mousePosition.y}px)` }}
        ></div>
        <div 
          className="orb orb-3"
        ></div>
      </div>

      {/* --- Tarjeta Glassmorphic --- */}
      <div className="card-wrapper">
        <div className="card-border-glow"></div>
        
        <div className="glass-card">
          {/* Brillo interior al hacer hover */}
          <div className="card-shine"></div>

          {/* --- Cabecera --- */}
          <div className="card-header">
            <div className="logo-container">
              <GraduationCap size={32} className="logo-icon" />
            </div>
            <h1 className="title">Portal Académico</h1>
            <p className="subtitle">Ingresa tus credenciales para acceder</p>
          </div>

          {/* --- Formulario --- */}
          <form onSubmit={handleSubmit} className="login-form">
            
            {/* Input Email */}
            <div className="input-group">
              <label>Email o Matrícula</label>
              <div className="input-wrapper">
                <div className="input-icon">
                  <Mail size={18} />
                </div>
                <input
                  type="text"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="form-input"
                  placeholder="ejemplo@universidad.edu"
                />
              </div>
            </div>

            {/* Input Password */}
            <div className="input-group">
              <div className="label-row">
                <label>Contraseña</label>
                <a href="#" className="forgot-link">¿Olvidaste tu contraseña?</a>
              </div>
              <div className="input-wrapper">
                <div className="input-icon">
                  <Lock size={18} />
                </div>
                <input
                  type={showPassword ? "text" : "password"}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="form-input password-input"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="toggle-password"
                >
                  {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>

            {/* Info Box */}
            <div className="info-box">
              <Info className="info-icon" size={18} />
              <p>
                Si eres nuevo ingreso, tu contraseña inicial es tu <span>número de cédula</span> (sin guiones).
              </p>
            </div>

            {/* Checkbox */}
            <div className="checkbox-container">
              <label className="checkbox-label">
                <div className="checkbox-wrapper">
                  <input
                    type="checkbox"
                    checked={rememberMe}
                    onChange={() => setRememberMe(!rememberMe)}
                  />
                  <div className={`custom-checkbox ${rememberMe ? 'checked' : ''}`}>
                    <Check size={12} className={`check-icon ${rememberMe ? 'visible' : ''}`} />
                  </div>
                </div>
                <span className="checkbox-text">Mantener sesión activa</span>
              </label>
            </div>

            {/* Botón Principal */}
            <button
              type="button"
              onClick={handleSubmit}
              className={`btn-primary ${isLoading ? 'loading' : ''}`}
            >
              <div className="btn-shine"></div>
              <span className="btn-content">
                {isLoading ? (
                  <>
                    <svg className="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="spinner-track" cx="12" cy="12" r="10"></circle>
                      <path className="spinner-fill" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Ingresando...
                  </>
                ) : (
                  <>
                    Ingresar al Portal
                    <LogIn size={18} />
                  </>
                )}
              </span>
            </button>
          </form>

          {/* --- Divider --- */}
          <div className="divider">
            <div className="divider-line"></div>
            <div className="divider-text-wrapper">
              <span className="divider-text">¿Aún no eres estudiante?</span>
            </div>
          </div>

          {/* --- Botón Secundario --- */}
          <button
            type="button"
            className="btn-secondary"
          >
            <UserPlus size={18} className="secondary-icon" />
            Solicitar Admisión / Nuevo Ingreso
          </button>

        </div>
      </div>

      <style>{`
        /* Reset & Base */
        * { box-sizing: border-box; }
        
        .login-container {
          min-height: 100vh;
          width: 100%;
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          overflow: hidden;
          background-color: #0f172a;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
          color: white;
        }

        /* Background Effects */
        .background-wrapper {
          position: absolute;
          inset: 0;
          width: 100%;
          height: 100%;
          pointer-events: none;
          background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%);
        }

        .orb {
          position: absolute;
          border-radius: 50%;
          mix-blend-mode: screen;
        }

        .orb-1 {
          top: -10%;
          left: -10%;
          width: 500px;
          height: 500px;
          background: rgba(147, 51, 234, 0.3); /* Purple */
          filter: blur(100px);
          animation: float-slow 8s ease-in-out infinite;
        }

        .orb-2 {
          bottom: -10%;
          right: -10%;
          width: 600px;
          height: 600px;
          background: rgba(79, 70, 229, 0.3); /* Indigo */
          filter: blur(120px);
          animation: float-medium 6s ease-in-out infinite;
        }

        .orb-3 {
          top: 40%;
          left: 60%;
          width: 300px;
          height: 300px;
          background: rgba(219, 39, 119, 0.2); /* Pink */
          filter: blur(80px);
          animation: float-fast 4s ease-in-out infinite;
        }

        /* Card Wrapper & Glass Effect */
        .card-wrapper {
          position: relative;
          z-index: 10;
          width: 100%;
          max-width: 450px;
          padding: 4px;
        }

        .card-border-glow {
          position: absolute;
          inset: 0;
          background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1), transparent);
          border-radius: 24px;
          filter: blur(1px);
        }

        .glass-card {
          position: relative;
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(24px);
          -webkit-backdrop-filter: blur(24px);
          border: 1px solid rgba(255, 255, 255, 0.2);
          box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
          border-radius: 24px;
          padding: 40px;
          color: white;
          overflow: hidden;
        }

        /* Hover Shine Effect */
        .card-shine {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: linear-gradient(135deg, rgba(255,255,255,0.05), transparent);
          opacity: 0;
          transition: opacity 0.7s;
          pointer-events: none;
        }
        .glass-card:hover .card-shine {
          opacity: 1;
        }

        /* Header */
        .card-header {
          text-align: center;
          margin-bottom: 32px;
          position: relative;
        }

        .logo-container {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 64px;
          height: 64px;
          border-radius: 16px;
          background: linear-gradient(to top right, #6366f1, #a855f7);
          box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
          margin-bottom: 16px;
          transition: transform 0.5s;
        }
        .logo-container:hover {
          transform: scale(1.1) rotate(3deg);
        }
        .logo-icon { color: white; }

        .title {
          font-size: 1.875rem;
          font-weight: 700;
          letter-spacing: -0.025em;
          margin: 0;
          background: linear-gradient(to right, #ffffff, #e0e7ff, #c7d2fe);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .subtitle {
          color: rgba(199, 210, 254, 0.8);
          margin-top: 8px;
          font-size: 0.875rem;
          font-weight: 500;
        }

        /* Form */
        .login-form {
          position: relative;
          z-index: 20;
          display: flex;
          flex-direction: column;
          gap: 24px;
        }

        .input-group label {
          display: block;
          font-size: 0.875rem;
          font-weight: 500;
          color: #e0e7ff;
          margin-bottom: 8px;
          margin-left: 4px;
        }

        .label-row {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .forgot-link {
          font-size: 0.75rem;
          color: #a5b4fc;
          text-decoration: none;
          transition: color 0.2s;
        }
        .forgot-link:hover {
          color: white;
          text-decoration: underline;
        }

        .input-wrapper {
          position: relative;
        }

        .input-icon {
          position: absolute;
          top: 0;
          bottom: 0;
          left: 0;
          padding-left: 16px;
          display: flex;
          align-items: center;
          pointer-events: none;
          color: #a5b4fc;
          transition: color 0.3s;
        }
        .input-wrapper:focus-within .input-icon {
          color: white;
        }

        .form-input {
          width: 100%;
          padding: 12px 16px 12px 44px;
          background: rgba(255, 255, 255, 0.05);
          border: 1px solid rgba(255, 255, 255, 0.1);
          border-radius: 12px;
          outline: none;
          color: white;
          font-size: 1rem;
          transition: all 0.3s;
        }
        .form-input::placeholder {
          color: rgba(165, 180, 252, 0.3);
        }
        .form-input:focus {
          background: rgba(255, 255, 255, 0.1);
          border-color: transparent;
          box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.5);
        }
        .password-input {
          padding-right: 44px;
          letter-spacing: 0.1em;
        }

        .toggle-password {
          position: absolute;
          top: 0;
          bottom: 0;
          right: 0;
          padding-right: 16px;
          background: none;
          border: none;
          cursor: pointer;
          color: #a5b4fc;
          display: flex;
          align-items: center;
          transition: color 0.2s;
        }
        .toggle-password:hover {
          color: white;
        }

        /* Info Box */
        .info-box {
          background: rgba(99, 102, 241, 0.2);
          border: 1px solid rgba(129, 140, 248, 0.3);
          border-radius: 12px;
          padding: 16px;
          display: flex;
          gap: 12px;
          align-items: flex-start;
          backdrop-filter: blur(4px);
        }
        .info-icon {
          color: #a5b4fc;
          flex-shrink: 0;
          margin-top: 2px;
        }
        .info-box p {
          font-size: 0.75rem;
          color: #e0e7ff;
          margin: 0;
          line-height: 1.5;
        }
        .info-box span {
          font-weight: 700;
          color: white;
        }

        /* Checkbox */
        .checkbox-container {
          display: flex;
          align-items: center;
        }
        .checkbox-label {
          display: flex;
          align-items: center;
          cursor: pointer;
          user-select: none;
        }
        .checkbox-wrapper {
          position: relative;
        }
        .checkbox-wrapper input {
          position: absolute;
          opacity: 0;
          cursor: pointer;
          height: 0;
          width: 0;
        }
        .custom-checkbox {
          width: 20px;
          height: 20px;
          border-radius: 4px;
          border: 1px solid rgba(165, 180, 252, 0.5);
          background: rgba(255, 255, 255, 0.05);
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all 0.3s;
        }
        .checkbox-wrapper:hover .custom-checkbox {
          border-color: #a5b4fc;
        }
        .custom-checkbox.checked {
          background-color: #6366f1;
          border-color: #6366f1;
        }
        .check-icon {
          color: white;
          transform: scale(0);
          transition: transform 0.2s;
        }
        .check-icon.visible {
          transform: scale(1);
        }
        .checkbox-text {
          margin-left: 12px;
          font-size: 0.875rem;
          color: #c7d2fe;
          transition: color 0.2s;
        }
        .checkbox-label:hover .checkbox-text {
          color: white;
        }

        /* Primary Button */
        .btn-primary {
          width: 100%;
          position: relative;
          padding: 14px 16px;
          background: linear-gradient(to right, #4f46e5, #9333ea);
          color: white;
          font-weight: 700;
          border: none;
          border-radius: 12px;
          cursor: pointer;
          box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
          transition: all 0.3s;
          overflow: hidden;
        }
        .btn-primary:hover {
          background: linear-gradient(to right, #4338ca, #7e22ce);
          transform: translateY(-2px);
          box-shadow: 0 15px 20px -3px rgba(99, 102, 241, 0.6);
        }
        .btn-primary:active {
          transform: scale(0.98);
        }
        
        .btn-shine {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(255,255,255,0.2);
          border-radius: 12px;
          transform: translateY(100%);
          transition: transform 0.3s;
        }
        .btn-primary:hover .btn-shine {
          transform: translateY(0);
        }

        .btn-content {
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
        }

        /* Spinner Animation */
        .spinner {
          animation: spin 1s linear infinite;
          height: 20px;
          width: 20px;
          color: white;
        }
        .spinner-track { opacity: 0.25; stroke: currentColor; stroke-width: 4; }
        .spinner-fill { opacity: 0.75; }

        /* Divider */
        .divider {
          position: relative;
          margin: 32px 0;
        }
        .divider-line {
          position: absolute;
          top: 50%;
          left: 0;
          width: 100%;
          border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .divider-text-wrapper {
          position: relative;
          display: flex;
          justify-content: center;
        }
        .divider-text {
          font-size: 0.75rem;
          text-transform: uppercase;
          background: rgba(30, 32, 56, 0.6);
          backdrop-filter: blur(12px);
          padding: 4px 12px;
          color: #a5b4fc;
          border-radius: 9999px;
          border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Secondary Button */
        .btn-secondary {
          width: 100%;
          padding: 12px 16px;
          background: rgba(255, 255, 255, 0.05);
          border: 1px solid rgba(255, 255, 255, 0.1);
          color: #e0e7ff;
          font-weight: 600;
          border-radius: 12px;
          cursor: pointer;
          transition: all 0.3s;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
        }
        .btn-secondary:hover {
          background: rgba(255, 255, 255, 0.1);
          border-color: rgba(255, 255, 255, 0.3);
          color: white;
        }
        .secondary-icon {
          color: #818cf8;
          transition: color 0.3s;
        }
        .btn-secondary:hover .secondary-icon {
          color: white;
        }

        /* Animations Keyframes */
        @keyframes float-slow {
          0%, 100% { transform: translate(0, 0); }
          50% { transform: translate(20px, -20px); }
        }
        @keyframes float-medium {
          0%, 100% { transform: translate(0, 0); }
          50% { transform: translate(-15px, 25px); }
        }
        @keyframes float-fast {
          0%, 100% { transform: translate(0, 0); }
          50% { transform: translate(10px, 15px); }
        }
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}