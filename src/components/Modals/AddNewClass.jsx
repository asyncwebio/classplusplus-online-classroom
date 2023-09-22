import React, { useState } from "react";
import { v4 as uuidv4 } from "uuid";

import {
  Button,
  Collapse,
  Modal,
  Input,
  Space,
  Typography,
  Divider,
  Switch,
} from "antd";
const CreateClassModal = ({ open, handleCancel, handleOk }) => {
  const [className, setClassName] = useState("");
  const [enableRecording, setEnableRecording] = useState(true);
  const [accessCode, setAccessCode] = useState("");
  const [loading, setLoading] = useState(false);
  const [presentation, setPresentation] = useState("");
  const [muteUserOnJoin, setMuteUserOnJoin] = useState(false);
  const [require_moderator_approval, setRequireModeratorApproval] =
    useState(false);
  const [all_users_join_as_moderator, setAllUsersJoinAsModerator] =
    useState(false);
  const [logo_url, setLogoUrl] = useState("");
  const [logout_url, setLogoutUrl] = useState("");
  const [primary_color, setPrimaryColor] = useState("#0F70D7");
  const [welcome_message, setWelcomeMessage] = useState(
    "Use a headset to avoid causing background noise. Refresh the browser in case of any network issue."
  );
  const [enable_moderator_to_unmute_users, setEnableModeratorToUnmuteUsers] =
    useState(false);
  const [skip_check_audio, setSkipCheckAudio] = useState(false);
  const [disable_listen_only_mode, setDisableListenOnlyMode] = useState(false);
  const [enable_user_private_chats, setEnableUserPrivateChats] =
    useState(false);
  const [class_layout, setClassLayout] = useState("SMART_LAYOUT");
  const [additional_join_params, setAdditionalJoinParams] = useState("");
  const [error, setError] = useState("");
  const bbbId = uuidv4();
  const handleCreateClass = async () => {
    try {
      setLoading(true);
      const classData = {
        name: className,
        bbb_id: bbbId,
        record: enableRecording,
        presentation,
        access_code: accessCode,
        mute_user_on_join: muteUserOnJoin,
        require_moderator_approval,
        all_users_join_as_moderator,
        logo_url,
        logout_url,
        primary_color,
        welcome_message,
        enable_moderator_to_unmute_users,
        skip_check_audio,
        disable_listen_only_mode,
        enable_user_private_chats,
        class_layout,
        additional_join_params,
      };
      const baseUrl = document
        .getElementById("rest-api")
        .getAttribute("data-rest-endpoint");
      const response = await fetch(`${baseUrl}/create-class`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(classData),
      });
      if (!response.ok) {
        setError("Something went wrong. Please try again later.");
        return;
      }
      const { data } = await response.json();
      handleOk(data);
    } catch (error) {
      setError("Something went wrong. Please try again later.");
    } finally {
      setClassName("");
      setEnableRecording(false);
      setPresentation("");
      setMuteUserOnJoin(false);
      setRequireModeratorApproval(false);
      setAllUsersJoinAsModerator(false);
      setLogoUrl("");
      setLogoutUrl("");
      setPrimaryColor("");
      setWelcomeMessage("");
      setEnableModeratorToUnmuteUsers(false);
      setSkipCheckAudio(false);
      setDisableListenOnlyMode(false);
      setEnableUserPrivateChats(false);
      setClassLayout("DEFAULT");
      setAdditionalJoinParams("");
    }
  };

  return (
    <Modal
      title=""
      open={open}
      onOk={handleCancel}
      okButtonProps={{
        onClick: handleCreateClass,
        disabled: !className || loading,
        loading,
      }}
      onCancel={handleCancel}
      cancelButtonProps={{
        disabled: loading,
      }}
      okText={loading ? "Loading..." : "Create Class"}
      cancelText="Cancel"
    >
      <div
        style={{
          paddingBottom: "2rem",
        }}
      >
        <section>
          <Typography.Title level={5}>Add New Class</Typography.Title>
          <Space
            styles={{
              marginTop: "1rem",
            }}
            direction="vertical"
            size="large"
            style={{
              width: "100%",
            }}
          >
            <div>
              <label>Class Name</label>
              <Input
                placeholder="Enter Class Name"
                disabled={loading}
                value={className}
                onChange={(e) => {
                  setClassName(e.target.value);
                }}
              />
            </div>
            <div>
              <label>Access Code(Optional)</label>
              <Input
                placeholder="Enter Class Access Code"
                disabled={loading}
                value={accessCode}
                onChange={(e) => {
                  setAccessCode(e.target.value);
                }}
              />
            </div>
            <div>
              <label>Presentation URL(Optional)</label>
              <Input
                placeholder="Enter Presentation URL"
                value={presentation}
                disabled={loading}
                onChange={(e) => {
                  setPresentation(e.target.value);
                }}
              />
              <Typography.Text type="secondary">
                If available, your presentation above would be displayed in the
                presentation area of your online classroom.
              </Typography.Text>
            </div>
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
              }}
            >
              <label>Enable Recording</label>
              <Switch
                disabled={loading}
                checked={enableRecording}
                onChange={(checked) => setEnableRecording(checked)}
              />
            </div>
            {/* mute_user_on_join */}
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
              }}
            >
              <label>Mute User On Join</label>
              <Switch
                disabled={loading}
                checked={muteUserOnJoin}
                onChange={(checked) => setMuteUserOnJoin(checked)}
              />
            </div>
            {/* require_moderator_approval */}
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
              }}
            >
              <label>Require Moderator Approval</label>
              <Switch
                disabled={loading}
                checked={require_moderator_approval}
                onChange={(checked) => setRequireModeratorApproval(checked)}
              />
            </div>
            {/* all_users_join_as_moderator */}
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
              }}
            >
              <label>All Users Join As Moderator</label>
              <Switch
                disabled={loading}
                checked={all_users_join_as_moderator}
                onChange={(checked) => setAllUsersJoinAsModerator(checked)}
              />
            </div>

            {/* branding settings */}
            <div>
              <Divider />
              <Collapse
                bordered={false}
                items={[
                  {
                    key: "1",
                    label: <h4 style={{ margin: 0 }}>Branding Settings</h4>,
                    children: (
                      <Space
                        styles={{
                          marginTop: "1rem",
                        }}
                        direction="vertical"
                        size="large"
                        style={{
                          width: "100%",
                        }}
                      >
                        <div>
                          <label>Logo URL(Optional)</label>
                          <Input
                            placeholder="Enter Logo URL"
                            value={logo_url}
                            disabled={loading}
                            onChange={(e) => {
                              setLogoUrl(e.target.value);
                            }}
                          />
                          <Typography.Text type="secondary">
                            Logo will be displayed in the top left corner of the
                            BigBlueButton client only if it is enabled in the
                            server.
                          </Typography.Text>
                        </div>

                        {/* logout_url */}
                        <div>
                          <label>Logout URL(Optional)</label>
                          <Input
                            placeholder="Enter Logout URL"
                            value={logout_url}
                            disabled={loading}
                            onChange={(e) => {
                              setLogoutUrl(e.target.value);
                            }}
                          />
                          <Typography.Text type="secondary">
                            The logout URL is the URL that the BigBlueButton
                            client will redirect to when the user clicks the
                            logout button.
                          </Typography.Text>
                        </div>
                        {/* primary_color */}
                        <div>
                          <label>Primary Color(Optional)</label>
                          <div
                            style={{
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                            }}
                          >
                            <Input
                              style={{
                                width: "90%",
                              }}
                              placeholder="Enter Primary Color"
                              value={primary_color}
                              disabled={loading}
                              onChange={(e) => {
                                setPrimaryColor(e.target.value);
                              }}
                            />

                            {/* display small ceicle with selected color  */}
                            <div
                              style={{
                                width: "2rem",
                                height: "2rem",
                                borderRadius: "50%",
                                backgroundColor: primary_color,
                                border: "1px solid #ccc",
                              }}
                            ></div>
                          </div>
                          <Typography.Text type="secondary">
                            The primary color above will be used as the color of
                            different buttons in the classroom UI.
                          </Typography.Text>
                        </div>
                        {/* welcome_message */}
                        <div>
                          <label>Welcome Message(Optional)</label>
                          <Input.TextArea
                            placeholder="Enter Welcome Message"
                            value={welcome_message}
                            disabled={loading}
                            onChange={(e) => {
                              setWelcomeMessage(e.target.value);
                            }}
                          />
                          <Typography.Text type="secondary">
                            The welcome message is displayed in the chat window
                            when the user joins the session.
                          </Typography.Text>
                        </div>
                      </Space>
                    ),
                  },
                ]}
              />
              <Divider />
              {/* advanced settings */}
              <Collapse
                bordered={false}
                items={[
                  {
                    key: "2",
                    label: (
                      <h4
                        style={{
                          margin: 0,
                        }}
                      >
                        Advanced Settings
                      </h4>
                    ),
                    children: (
                      <Space
                        styles={{
                          marginTop: "1rem",
                        }}
                        direction="vertical"
                        size="large"
                        style={{
                          width: "100%",
                        }}
                      >
                        {/* enable_moderator_to_unmute_users */}
                        <div
                          style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <label>Enable Moderator To Unmute Users</label>
                          <Switch
                            disabled={loading}
                            checked={enable_moderator_to_unmute_users}
                            onChange={(checked) =>
                              setEnableModeratorToUnmuteUsers(checked)
                            }
                          />
                        </div>

                        {/* skip_check_audio */}
                        <div
                          style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <label>Skip Check Audio</label>
                          <Switch
                            disabled={loading}
                            checked={skip_check_audio}
                            onChange={(checked) => setSkipCheckAudio(checked)}
                          />
                        </div>

                        {/* disable_listen_only_mode */}
                        <div
                          style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <label>Disable Listen Only Mode</label>
                          <Switch
                            disabled={loading}
                            checked={disable_listen_only_mode}
                            onChange={(checked) =>
                              setDisableListenOnlyMode(checked)
                            }
                          />
                        </div>

                        {/* enable_user_private_chats */}
                        <div
                          style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <label>Enable User Private Chats</label>
                          <Switch
                            disabled={loading}
                            checked={enable_user_private_chats}
                            onChange={(checked) =>
                              setEnableUserPrivateChats(checked)
                            }
                          />
                        </div>

                        {/* class_layout */}
                        <div
                          style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <label>Class Layout</label>
                          <select
                            disabled={loading}
                            value={class_layout}
                            onChange={(e) => setClassLayout(e.target.value)}
                          >
                            <option value="SMART_LAYOUT">Default</option>
                            {/* presentation focused */}
                            <option value="PRESENTATION_FOCUS">
                              Presentation Focused
                            </option>
                            {/* video focused */}
                            <option value="VIDEO_FOCUS">Video Focused</option>
                          </select>
                        </div>

                        {/* additional_join_params */}
                        <div>
                          <label>Additional Join Params(Optional)</label>
                          <Input.TextArea
                            placeholder='{"webcamsOnlyForModerator":true, "bannerText":"",...}'
                            value={additional_join_params}
                            disabled={loading}
                            onChange={(e) => {
                              setAdditionalJoinParams(e.target.value);
                            }}
                          />
                          <Typography.Text type="secondary">
                            You can enter additional parameters to customize how
                            a class is created (
                            <a
                              href="https://docs.bigbluebutton.org/development/api/#create"
                              target="_blank"
                              rel="noreferrer"
                            >
                              API Doc
                            </a>
                            )
                          </Typography.Text>
                        </div>
                      </Space>
                    ),
                  },
                ]}
              />
            </div>
          </Space>
        </section>
      </div>
      {/* <Divider /> */}
      {error && (
        <Typography.Text type="danger" style={{ display: "block" }}>
          {error}
        </Typography.Text>
      )}
    </Modal>
  );
};
export default CreateClassModal;
