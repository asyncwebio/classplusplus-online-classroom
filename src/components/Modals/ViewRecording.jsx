import { useState, useEffect } from "react";
import { Button, Empty, Modal, Skeleton, Table, Tag } from "antd";
const ViewRecordingModal = ({ bbbId, bbbClassName, open, handleCancel }) => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [recording, setRecording] = useState([]);

  const getPlayBackUrl = ({ format }) => {
    let url = null;
    if (Array.isArray(format)) {
      const presentation = format.find((p) => p.type === "presentation");
      url = presentation?.url;
      return url;
    }
    url = format.url;
    return url;
  };

  const getDuration = (startTime, endTime) => {
    // get duration in hh:mm:ss from startTime and endTime timestamp
    const start = new Date(startTime);
    const end = new Date(endTime);
    const duration = end - start;
    const hours = parseInt(duration / 3600000);
    const minutes = parseInt((duration % 3600000) / 60000);
    const seconds = parseInt(((duration % 3600000) % 60000) / 1000);

    if (hours > 0) {
      return `${hours}h ${minutes}m ${seconds}s`;
    }
    return `${minutes}m ${seconds}s`;
  };

  const createRow = (data) => {
    console.log(data);
    if (data?.length == 0 || data[0] === undefined) {
      return [];
    }
    return data.map((d) => ({
      key: d.recordID,
      name: d.name,
      //   convert time start to dd/mm/yyyy hh:mm AM/PM
      startTime: new Date(parseInt(d.startTime)).toLocaleString("en-US", {
        dateStyle: "short",
        timeStyle: "short",
        hour12: true,
      }),
      duration: getDuration(parseInt(d.startTime), parseInt(d.endTime)),
      participants: d.participants,
      playback: (
        <Tag color="blue">
          <a href={getPlayBackUrl(d.playback)} target="_blank">
            View
          </a>
        </Tag>
      ),
    }));
  };

  const columns = [
    {
      title: "Name",
      dataIndex: "name",
      key: "name",
    },
    {
      title: "Start Time",
      dataIndex: "startTime",
      key: "startTime",
      width: "20%",
    },
    {
      title: "Duration",
      dataIndex: "duration",
      key: "duration",
      width: "20%",
    },
    {
      title: "Participants",
      dataIndex: "participants",
      key: "participants",
      width: "20%",
    },
    {
      title: "Recordings",
      dataIndex: "playback",
      key: "playback",
      width: "20%",
    },
  ];

  const fetchRecordings = async () => {
    try {
      setLoading(true);
      const baseUrl = document
        .getElementById("rest-api")
        .getAttribute("data-rest-endpoint");
      const delimiter = document
        .getElementById("rest-api")
        .getAttribute("data-delimiter");
      const response = await fetch(
        `${baseUrl}/get-recordings${delimiter}meetingID=${bbbId}`
      );
      if (!response.ok) {
        setError("Something went wrong. Please try again later.");
        console.log("Something went wrong. Please try again later.");
        return;
      }
      const { data } = await response.json();

      // check if data.recordings is an array
      if (!Array.isArray(data.recording)) {
        setRecording([data.recording]);
      } else {
        setRecording([...data.recording]);
      }
    } catch (error) {
      console.log(error);
      setError("Something went wrong. Please try again later.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchRecordings();
  }, []);

  return (
    <>
      <Modal
        title={`Recordings for ${bbbClassName}`}
        open={open}
        footer={[]}
        onCancel={handleCancel}
        width={1000}
      >
        <Table
          loading={loading}
          dataSource={createRow(recording)}
          pagination={false}
          columns={columns}
          scroll={{ y: 350 }}
        />
        {error && (
          <p
            style={{
              color: "red",
            }}
          >
            {error}
          </p>
        )}
      </Modal>
    </>
  );
};
export default ViewRecordingModal;
