import React, { useState, useEffect, useContext } from 'react';
import FileContext from './FileContext';
import axios from 'axios';


const FileManagement = () => {
   const [files, setFiles] = useState([]);
   const [selectedFile, setSelectedFile] = useState(null);
   const [fileType, setFileType] = useState('sql');

   const { selectedFileData, setSelectedFileData } = useContext(FileContext);


   const handleFileClick = (file) => {
      fetchFileData(file.id); // Загрузка данных файла
   };

   const fetchFileData = (fileId) => {
      axios.get(`/api/files/${fileId}`)
         .then(response => {
            setSelectedFileData(response.data);
         })
         .catch(error => console.error("There was an error fetching the file data!", error));
   };

   useEffect(() => {
      fetchFiles();
   }, []);

   const fetchFiles = () => {
      axios.get('/api/files')
         .then(response => {
            setFiles(response.data);
         })
         .catch(error => console.error("There was an error fetching the files!", error));
   };

   const handleFileChange = (event) => {
      setSelectedFile(event.target.files[0]);
   };

   const handleFileTypeChange = (event) => {
      setFileType(event.target.value);
   };

   const handleFileUpload = () => {
      const formData = new FormData();
      formData.append('file', selectedFile);
      formData.append('fileType', fileType);

      axios.post('/api/files', formData, {
         headers: {
            'Content-Type': 'multipart/form-data',
         }
      })
         .then(() => {
            fetchFiles();  // Refresh the list of files after the upload
         })
         .catch(error => console.error("There was an error uploading the file!", error));
   };

   const handleFileDelete = (event, fileId) => {
      event.preventDefault();
      event.stopPropagation();

      axios.delete(`/api/files/${fileId}`)
         .then(() => {
            fetchFiles();  // Refresh the list of files after the deletion
         })
         .catch(error => console.error("There was an error deleting the file!", error));
   };
   return (
      <div>
         <div>
            <input type="file" onChange={handleFileChange} />
            <select value={fileType} onChange={handleFileTypeChange}>
               <option value="sql">SQL Dump</option>
               <option value="log/apache2">Apache Log</option>
            </select>
            <button onClick={handleFileUpload}>Upload</button>
         </div>
         <div>
            <h2>Загруженные файлы</h2>
            <ul className="file-list">
               {files.map(file => (
                  <li key={file.id} className="file-item">
                     <span className="file-name" onClick={() => handleFileClick(file)}>
                        {file.name}
                     </span>
                     <span className="file-type">
                        ({file.file_type})
                     </span>
                     <button className="delete-button" onClick={(event) => handleFileDelete(event, file.id)}>
                        Delete
                     </button>
                  </li>
               ))}
            </ul>
         </div>
      </div>

   );
};

export default FileManagement;
