import { useState, useRef } from "react";
import { Card, Dropdown, Button, Space, Tag, Popconfirm } from "antd";

import ViewRecordingModal from "./Modals/ViewRecording";

import {
  MoreOutlined,
  HomeOutlined,
  EditOutlined,
  VideoCameraOutlined,
  DeleteOutlined,
} from "@ant-design/icons";

const ClassCard = ({ data, handleDeleteClass, handleEditClick }) => {
  const [deleting, setDeleting] = useState(false);
  const [starting, setStarting] = useState(false);
  const [isViewRecordingModalOpen, setIsViewRecordingModalOpen] =
    useState(false);
  const [inviteCopied, setInviteCopied] = useState(false);
  const inviteLinkRef = useRef(null);

  //copy to clipboard
  const copyToClipboard = () => {
    const baseUrl = document
      .getElementById("rest-api")
      .getAttribute("data-rest-endpoint");
    const delimiter = document
      .getElementById("rest-api")
      .getAttribute("data-delimiter");

    const inviteLink = `${baseUrl}/join-class${delimiter}id=${data.id}${
      data.access_code ? "&access_code=" + data.access_code : ""
    }&join_name=`;
    const el = document.createElement("textarea");
    el.value = inviteLink;
    document.body.appendChild(el);
    el.select();
    document.execCommand("copy");
    document.body.removeChild(el);
    inviteLinkRef.current.innerText = "Copied!";
    setInviteCopied(true);
    setTimeout(() => {
      inviteLinkRef.current.innerText = "Invite";
      setInviteCopied(false);
    }, 2000);
  };
  const items = [
    {
      label: "Edit",
      key: "0",
      icon: <EditOutlined />,
      onClick: () => handleEditClick(data),
    },
    {
      label: "Recordings",
      key: "1",
      icon: <VideoCameraOutlined />,
      disabled: data?.record == "0",
      onClick: () => setIsViewRecordingModalOpen(true),
    },
    {
      type: "divider",
    },
    {
      label: (
        <Popconfirm
          title={`Delete ${data.name}?`}
          description="Are you sure you want to delete this class?"
          onConfirm={deleteClass}
        >
          Delete
        </Popconfirm>
      ),
      key: "3",
      icon: <DeleteOutlined />,
      disabled: deleting,
    },
  ];

  async function deleteClass() {
    setDeleting(true);
    await handleDeleteClass(data.id);
  }

  async function startClass(id) {
    try {
      setStarting(true);
      const baseUrl = document
        .getElementById("rest-api")
        .getAttribute("data-rest-endpoint");

      const delimiter = document
        .getElementById("rest-api")
        .getAttribute("data-delimiter");

      const response = await fetch(
        `${baseUrl}/start-class${delimiter}id=${id}`,
        { method: "POST" }
      );
      if (!response.ok) {
        return;
      }
      const { data } = await response.json();
      if (data) window.open(data, "_blank");
    } catch (error) {
      console.log(error);
      alert(error.message || "Something went wrong. Please try again later.");
    } finally {
      setStarting(false);
    }
  }

  return (
    <div className="card-wrapper ">
      <Card bordered={false} style={{ width: "24rem", padding: "0" }}>
        <div className="class-card-header">
          <div
            style={{
              display: "flex",
              "align-items": "start",
              "justify-content": "flex-start",
            }}
          >
            <Space size="1rem">
              <Tag
                style={{
                  padding: "0.9rem 1rem",
                  marginRight: "1rem",
                }}
              >
                <HomeOutlined
                  style={{
                    fontSize: "1.2rem",
                  }}
                />
              </Tag>
            </Space>
            <Space>
              <div
                style={{
                  display: "flex",
                  "align-items": "start",
                  flexDirection: "column",
                  "justify-content": "flex-start",
                }}
              >
                <h3
                  style={{
                    margin: "0",
                    padding: "0",
                  }}
                >
                  {data.name}
                </h3>
                <p
                  style={{
                    margin: "0",
                    padding: "0",
                  }}
                >
                  Last Session: {data.last_session}
                </p>
              </div>
            </Space>
          </div>
          <Dropdown
            menu={{
              items,
            }}
            trigger={["click"]}
          >
            <Button
              className="class-card-more-option"
              icon={<MoreOutlined />}
            />
          </Dropdown>
        </div>

        <div className="class-card-action">
          <Button
            type="primary"
            size="large"
            disabled={starting}
            loading={starting}
            onClick={() => startClass(data.id)}
          >
            Start BigBlueButton
          </Button>
          <Button
            ref={inviteLinkRef}
            onClick={copyToClipboard}
            disabled={inviteCopied}
            type="link"
          >
            Invite
          </Button>
        </div>
      </Card>
      {isViewRecordingModalOpen ? (
        <ViewRecordingModal
          bbbId={data.bbb_id}
          bbbClassName={data.name}
          open={isViewRecordingModalOpen}
          handleCancel={() => setIsViewRecordingModalOpen(false)}
        />
      ) : null}
    </div>
  );
};

export default ClassCard;
