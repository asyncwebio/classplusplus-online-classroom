import { useState, useEffect } from "react";
import {
    Button,
    Modal,
    Input,
    Space,
    Typography,
    Divider,
    Skeleton,
} from "antd";

const SettingsModal = ({ open, handleCancel }) => {
    const [bbbServerUrl, setBbbServerUrl] = useState("");
    const [bbbServerSecret, setBbbServerSecret] = useState("");
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);

    const fetchSettings = () => {
        setLoading(true);
        const baseUrl = document
            .getElementById("rest-api")
            .getAttribute("data-rest-endpoint");
        fetch(baseUrl + "/get-settings/")
            .then(async (res) => {
                if (res.ok) {
                    const { data } = await res.json();

                    const bbbURL = data?.bbbServerUrl;
                    const bbbSecret = data?.bbbServerSecret;
                    setBbbServerUrl(bbbURL);
                    setBbbServerSecret(bbbSecret);
                }
            })
            .catch((err) => {
                setError(err.message);
            })
            .finally(() => {
                setLoading(false);
            });
    };

    const handleSaveSettings = () => {
        setSaving(true);
        const baseUrl = document
            .getElementById("rest-api")
            .getAttribute("data-rest-endpoint");
        fetch(baseUrl + "/save-settings/", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                bbbServerUrl,
                bbbServerSecret,
            }),
        })
            .then((res) => {
                if (res.ok) {
                    setSaving(false);
                    handleCancel();
                } else {
                    res.json().then(({ message }) => {
                        setError(message);
                        setSaving(false);
                    });
                }
            })
            .catch((err) => {
                setError(err.message);
                setSaving(false);
            })
            .finally(() => {
                setSaving(false);
            });
    };

    useEffect(() => {
        fetchSettings();
    }, []);

    return (
        <>
            <Modal
                title=""
                open={open}
                onOk={handleCancel}
                okButtonProps={{
                    onClick: handleSaveSettings,
                    disabled: !(bbbServerUrl && bbbServerSecret) || loading || saving,
                }}
                onCancel={handleCancel}
                cancelButtonProps={{
                    disabled: loading || saving,
                }}
                okText={saving ? "Saving..." : "Save"}
                cancelText="Cancel"
            >
                {loading ? (
                    <Skeleton active />
                ) : (
                    <section>
                        <Typography.Title level={5}>
                            Online Classroom Settings
                        </Typography.Title>

                        {/* Add a link Don’t have a BigBlueButton server? Get BigBlueButton at 40% lower cost */}
                        <Typography.Link
                            href="https://higheredlab.com/"
                            target="_blank"
                        >
                            Don’t have a BigBlueButton server? Start Free Trial.
                        </Typography.Link>

                        <Space
                            direction="vertical"
                            size={12}
                            style={{
                                marginTop: "1rem",
                                width: "100%",
                            }}
                        >
                            <div>
                                <label>BigBlueButton Server URL</label>
                                <Input
                                    placeholder="https://example.com/bigbluebutton/api"
                                    value={bbbServerUrl}
                                    disabled={saving}
                                    onChange={(e) => setBbbServerUrl(e.target.value)}
                                />
                            </div>
                            <div>
                                <label>BigBlueButton Server Secret</label>
                                <Input
                                    type="password"
                                    placeholder="xxxxxxxxxxxxx"
                                    value={bbbServerSecret}
                                    disabled={saving}
                                    onChange={(e) => setBbbServerSecret(e.target.value)}
                                />
                            </div>
                            <label>You can also enter URL and Secret of any self-hosted or 3rd-party BigBlueButton server.</label>
                        </Space>
                    </section>
                )}
                {/* <Divider /> */}
                {/* error message */}
                {error && (
                    <div
                        style={{
                            color: "red",
                            marginTop: "1rem",
                        }}
                    >
                        {error}
                    </div>
                )}
            </Modal>
        </>
    );
};
export default SettingsModal;
