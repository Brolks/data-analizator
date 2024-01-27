import { createRoot } from 'react-dom/client';
import React from 'react';
import DateAnalyzer from './DateAnalyzer/DateAnalyzer';
import axios from 'axios';



axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('#date-analyzer-root').getAttribute('data-csrf-token');


const container = document.getElementById('date-analyzer-root');

// Создайте корень
const root = createRoot(container);

// Используйте root.render вместо ReactDOM.render
root.render(<DateAnalyzer />);
