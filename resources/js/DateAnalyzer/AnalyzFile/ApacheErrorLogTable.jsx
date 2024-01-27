// ApacheErrorLogTable.jsx
import React, { useState } from 'react';
import CustomTable from './CustomTable';
import Modal from '../Modal';

const ApacheErrorLogTable = ({ data }) => {
   const [showModal, setShowModal] = useState(false);
   const [modalContent, setModalContent] = useState(null);

   const handleShowMessage = (message) => {
      setModalContent(message);
      setShowModal(true);
   };

   const handleShowErrors = (errors) => {
      const errorsContent = (
         <div>
            {errors.map((error, index) => (
               <div key={index}>
                  <strong>{error.type}</strong>: {error.message}
                  <br />
                  {error.file && (
                     <span>File: {error.file}{error.line ? `, Line: ${error.line}` : ''}</span>
                  )}
               </div>
            ))}
         </div>
      );
      setModalContent(errorsContent);
      setShowModal(true);
   };


   const columns = React.useMemo(
      () => [
         { Header: 'Datetime', accessor: 'datetime' },
         { Header: 'ErrorLevel', accessor: 'errorLevel' },
         { Header: 'Module', accessor: 'module' },
         { Header: 'Pid', accessor: 'pid' },
         { Header: 'Client', accessor: 'client' },
         {
            Header: 'Message',
            accessor: 'message',
            Cell: ({ value }) => (
               <div>
                  {value.length > 100 ? (
                     <>
                        {`${value.substring(0, 100)}...`}
                        <button onClick={() => handleShowMessage(value)}>Подробнее</button>
                     </>
                  ) : (
                     value // Если сообщение короткое, просто отображаем его
                  )}
               </div>
            )
         },
         {
            Header: 'Errors',
            accessor: 'errors',
            Cell: ({ value }) => (
               value && value.length > 0 ? (
                  <button onClick={() => handleShowErrors(value)}>Показать ошибки</button>
               ) : null // Если ошибок нет, не отображаем кнопку
            )
         },
      ],
      []
   );

   return (
      <div>
         <CustomTable columns={columns} data={data} />
         {showModal && (
            <Modal onClose={() => setShowModal(false)}>
               {modalContent}
            </Modal>
         )}
      </div>
   );
};

export default ApacheErrorLogTable;
