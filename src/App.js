import React, { useState } from "react";
import Header from "./components/Header";
import Dashboard from "./components/Dashboard";
import SettingsModal from "./components/Modals/Settings";
import CreateClassModal from "./components/Modals/AddNewClass";

const App = () => {
  const [allClasses, setAllClasses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [isSettingsModalOpen, setIsSettingsModalOpen] = useState(false);
  const handleSttingsModalOpen = () => setIsSettingsModalOpen(true);
  const handleSttingsModalClose = () => setIsSettingsModalOpen(false);
  const [isCreateClassModalOpen, setIsCreateClassModalOpen] = useState(false);
  const handleCreateClassModalOpen = () => setIsCreateClassModalOpen(true);
  const handleCreateClassModalClose = () => setIsCreateClassModalOpen(false);
  const fetchAllClasses = async () => {
    try {
      setLoading(true);
      const baseUrl = document.getElementById("rest-api").getAttribute("data-rest-endpoint")
      const response = await fetch(`${baseUrl}/get-classes`);
      if (response.ok) {
        const { data } = await response.json();
        setAllClasses(data);
      }
      setLoading(false);
    } catch (error) {
      setLoading(false);
      console.log(error);
    }
    finally {
      setLoading(false);
    }
  }


  React.useEffect(() => {
    if (allClasses?.length === 0) fetchAllClasses()
  }, [])
  return (
    <>
      <Header handleSttingsModalOpen={handleSttingsModalOpen} handleCreateClassModalOpen={handleCreateClassModalOpen} loading={loading} />
      <Dashboard bbbClasses={allClasses} setBBBClasses={setAllClasses} loading={loading} />
      {
        isSettingsModalOpen && <SettingsModal handleCancel={handleSttingsModalClose} open={isSettingsModalOpen} handleOk={handleCreateClassModalOpen} />
      }
      {
        isCreateClassModalOpen && <CreateClassModal handleCancel={handleCreateClassModalClose} open={isCreateClassModalOpen} handleOk={(d) => {
          setAllClasses([
            d,
            ...allClasses
          ])
          handleCreateClassModalClose();
        }} />
      }
    </>
  );
};

export default App;


