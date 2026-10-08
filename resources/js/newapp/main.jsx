import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';
import './styles/theme.css';

const el = document.getElementById('newapp-root');

if (el) {
  let bootstrap = null;
  try {
    bootstrap = JSON.parse(el.dataset.bootstrap || 'null');
  } catch {
    bootstrap = null;
  }
  createRoot(el).render(<App bootstrap={bootstrap} />);
}
