// resources/js/DateAnalyzer/DateAnalyzer.jsx

import React, {useState} from 'react';
import FileManagement from './FileManagement';
import AnalyzFile from './AnalyzFile';
import FileContext from './FileContext'; // Импортируйте контекст

const DateAnalyzer = () => {
   const [selectedFileData, setSelectedFileData] = useState(null);

   return (
       <FileContext.Provider value={{ selectedFileData, setSelectedFileData }}>
           <div style={{ display: 'flex', justifyContent: 'space-between' }}>
               <div style={{ width: '30%' }}>
                   <FileManagement />
               </div>
               <div style={{ width: '70%' }}>
                   <AnalyzFile />
               </div>
           </div>
       </FileContext.Provider>
   );
};

export default DateAnalyzer;