// FileContext.js
import { createContext } from 'react';

const FileContext = createContext({
    selectedFileData: null,
    setSelectedFileData: null // Теперь здесь null
});

export default FileContext;
