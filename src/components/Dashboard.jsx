import { useState } from "react";
import { Empty, Spin } from "antd";
import ClassCard from "./ClassCard";
import EditClassModal from "./Modals/EditClass";

const Dashboard = ({ bbbClasses, setBBBClasses, loading }) => {
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [editModalData, setEditModalData] = useState(null);

  const handleEditClick = (data) => {
    setEditModalData(data);
    setEditModalOpen(true);
  };

  const handleEditSave = (data) => {
    const newClassesList = bbbClasses.map((c) => {
      if (c.id === data.id) {
        return data;
      }
      return c;
    });
    setBBBClasses(newClassesList);
    setEditModalOpen(false);
    setEditModalData(null);
  };

  const handleDeleteClass = async (id) => {
    try {
      const baseUrl = document
        .getElementById("rest-api")
        .getAttribute("data-rest-endpoint");

      const delimiter = document
        .getElementById("rest-api")
        .getAttribute("data-delimiter");

      const response = await fetch(
        `${baseUrl}/delete-class${delimiter}id=${id}`,
        {
          method: "DELETE",
        }
      );
      if (!response.ok) {
        return;
      }
      const newClassesList = bbbClasses.filter((c) => c.id !== id);
      setBBBClasses(newClassesList);
    } catch (error) {
      console.log(error);
      alert(error.message || "Something went wrong. Please try again later.");
    }
  };

  const EmptyState = () => {
    return (
      <Empty
        description={
          <span
            style={{
              fontSize: "1.2rem",
            }}
          >
            {loading ? (
              "Loading..."
            ) : (
              <span>
                No classes created yet. Click on the <b>Add New Class</b> button
                to create a new class.
              </span>
            )}
          </span>
        }
      >
        {loading ? <Spin spinning={loading} /> : null}
      </Empty>
    );
  };
  return (
    <>
      {bbbClasses && bbbClasses.length == 0 ? (
        <div
          style={{
            marginTop: "10%",
          }}
        >
          <EmptyState />
        </div>
      ) : (
        <div
          style={{
            display: "flex",
            flexWrap: "wrap",
            justifyContent: "flex-start",
          }}
        >
          {bbbClasses.map((data) => (
            <>
              <ClassCard
                key={data.bbb_id}
                data={data}
                handleDeleteClass={handleDeleteClass}
                handleEditClick={handleEditClick}
              />
            </>
          ))}
        </div>
      )}
      {editModalOpen && (
        <EditClassModal
          handleOk={handleEditSave}
          open={editModalOpen}
          modalData={editModalData}
          handleCancel={() => {
            setEditModalOpen(false);
            setEditModalData(null);
          }}
        />
      )}
    </>
  );
};

export default Dashboard;
