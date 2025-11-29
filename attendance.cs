using DPFP;
using System;
using System.Windows.Forms;
using MySql.Data.MySqlClient;
using System.IO;
using System.Linq;
using System.Collections.Generic;
using System.Drawing;

namespace BiometricsFingerprint
{
    public partial class attendance : capture
    {
        private ComboBox eventComboBox;
        private Label eventLabel;
        private Button proceedButton;
        private Label statusLabel;
        private int selectedEventId = 0;
        private List<EventData> events = new List<EventData>();
        
        // Enhanced UI elements
        private Panel eventDetailsPanel;
        private Label eventDetailsLabel;
        private ProgressBar progressBar;
        private Label progressLabel;
        private Panel studentInfoPanel;
        private TextBox uidTextBox;
        private TextBox nameTextBox;
        private TextBox courseTextBox;
        private TextBox yearTextBox;
        private Button startScanButton;
        private Panel scanStatusPanel;
        private Label scanStatusLabel;
        private PictureBox statusIcon;

        public class EventData
        {
            public int event_id { get; set; }
            public string event_name { get; set; }
            public string date { get; set; }
            public string start_time { get; set; }
            public string end_time { get; set; }
            public string location { get; set; }
            public string description { get; set; }
            public bool is_mandatory { get; set; }
        }

        public class StudentData
        {
            public int id { get; set; }
            public string uid { get; set; }
            public string student_name { get; set; }
            public string course { get; set; }
            public string year_level { get; set; }
        }

        public class AttendanceData
        {
            public string status { get; set; }
            public string check_in_time { get; set; }
            public int minutes_late { get; set; }
        }

        public attendance()
        {
            // SET TO FULL SCREEN
            this.WindowState = FormWindowState.Maximized;
            this.Load += (sender, e) => { this.WindowState = FormWindowState.Maximized; };
            this.BackColor = Color.FromArgb(240, 248, 255);
            this.Text = "Student Attendance - Event Selection";
            SetupUI();
            LoadEvents();
        }

        private void SetupUI()
        {
            // Create event selection UI
            eventLabel = new Label()
            {
                Text = "Select Event:",
                Location = new System.Drawing.Point(50, 50),
                Size = new System.Drawing.Size(100, 23),
                Font = new System.Drawing.Font("Arial", 12, System.Drawing.FontStyle.Bold)
            };

            eventComboBox = new ComboBox()
            {
                Location = new System.Drawing.Point(160, 50),
                Size = new System.Drawing.Size(300, 23),
                DropDownStyle = ComboBoxStyle.DropDownList,
                Font = new System.Drawing.Font("Arial", 10)
            };

            proceedButton = new Button()
            {
                Text = "Proceed to Fingerprint Scan",
                Location = new System.Drawing.Point(480, 50),
                Size = new System.Drawing.Size(200, 30),
                BackColor = System.Drawing.Color.DarkGreen,
                ForeColor = System.Drawing.Color.White,
                Font = new System.Drawing.Font("Arial", 10, System.Drawing.FontStyle.Bold),
                Enabled = false
            };

            statusLabel = new Label()
            {
                Text = "Please select an event first",
                Location = new System.Drawing.Point(50, 90),
                Size = new System.Drawing.Size(600, 23),
                Font = new System.Drawing.Font("Arial", 10),
                ForeColor = System.Drawing.Color.Blue
            };

            // Add controls to form
            this.Controls.Add(eventLabel);
            this.Controls.Add(eventComboBox);
            this.Controls.Add(proceedButton);
            this.Controls.Add(statusLabel);

            // Event handlers
            eventComboBox.SelectedIndexChanged += EventComboBox_SelectedIndexChanged;
            proceedButton.Click += ProceedButton_Click;
        }

        private void EventComboBox_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (eventComboBox.SelectedItem != null)
            {
                var selectedEvent = (EventData)eventComboBox.SelectedItem;
                selectedEventId = selectedEvent.event_id;
                proceedButton.Enabled = true;
                statusLabel.Text = $"Selected: {selectedEvent.event_name} on {selectedEvent.date} at {selectedEvent.location}";
                statusLabel.ForeColor = System.Drawing.Color.Green;
            }
            else
            {
                selectedEventId = 0;
                proceedButton.Enabled = false;
                statusLabel.Text = "Please select an event first";
                statusLabel.ForeColor = System.Drawing.Color.Blue;
            }
        }

        private void ProceedButton_Click(object sender, EventArgs e)
        {
            if (selectedEventId > 0)
            {
                // Hide event selection UI and show fingerprint scanner
                eventLabel.Visible = false;
                eventComboBox.Visible = false;
                proceedButton.Visible = false;
                statusLabel.Text = "Place your finger on the scanner for attendance";
                statusLabel.ForeColor = System.Drawing.Color.DarkGreen;
                statusLabel.Font = new System.Drawing.Font("Arial", 12, System.Drawing.FontStyle.Bold);
            }
        }

        private void LoadEvents()
        {
            try
            {
                string connectionString = "server=localhost;user id=root;password=;database=biometric;SslMode=None;";
                string query = "SELECT event_id, event_name, date, start_time, end_time, location, description, is_mandatory " +
                              "FROM admin_event WHERE date >= CURDATE() ORDER BY date ASC, start_time ASC";

                using (var connection = new MySqlConnection(connectionString))
                using (var command = new MySqlCommand(query, connection))
                {
                    connection.Open();
                    using (var reader = command.ExecuteReader())
                    {
                        events.Clear();
                        while (reader.Read())
                        {
                            events.Add(new EventData
                            {
                                event_id = reader.GetInt32("event_id"),
                                event_name = reader.GetString("event_name"),
                                date = reader.GetString("date"),
                                start_time = reader.GetString("start_time"),
                                end_time = reader.GetString("end_time"),
                                location = reader.IsDBNull("location") ? "" : reader.GetString("location"),
                                description = reader.IsDBNull("description") ? "" : reader.GetString("description"),
                                is_mandatory = reader.GetBoolean("is_mandatory")
                            });
                        }
                    }
                }

                // Update UI on main thread
                if (this.InvokeRequired)
                {
                    this.Invoke(new Action(() => PopulateEventComboBox()));
                }
                else
                {
                    PopulateEventComboBox();
                }
            }
            catch (Exception ex)
            {
                ShowError("Error loading events: " + ex.Message);
            }
        }

        private void PopulateEventComboBox()
        {
            eventComboBox.Items.Clear();
            
            foreach (var evt in events)
            {
                string displayText = $"{evt.event_name} - {evt.date} ({evt.start_time} - {evt.end_time})";
                eventComboBox.Items.Add(evt);
                eventComboBox.DisplayMember = "event_name";
            }
            
            if (events.Count > 0)
            {
                statusLabel.Text = $"Loaded {events.Count} active events. Please select one.";
            }
            else
            {
                statusLabel.Text = "No active events found.";
                statusLabel.ForeColor = System.Drawing.Color.Orange;
            }
        }

        private void ShowError(string message)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action(() => {
                    statusLabel.Text = message;
                    statusLabel.ForeColor = System.Drawing.Color.Red;
                }));
            }
            else
            {
                statusLabel.Text = message;
                statusLabel.ForeColor = System.Drawing.Color.Red;
            }
        }

        protected override void Init()
        {
            base.Init();
            this.Text = "Student Attendance - Event Selection";
            // ENSURE FULL SCREEN
            this.WindowState = FormWindowState.Maximized;
            SetStatus("Select an event to proceed with attendance");
            SafeMakeReport("Choose an event from the dropdown above");
        }

        protected override void Process(Sample sample)
        {
            // Only process fingerprint if an event is selected and UI is hidden
            if (selectedEventId == 0 || proceedButton.Visible)
            {
                SafeMakeReport("Please select an event first");
                return;
            }

            base.Process(sample);
            SafeMakeReport("Processing fingerprint for attendance...");

            try
            {
                // Extract features for VERIFICATION
                var features = ExtractFeatures(sample, DPFP.Processing.DataPurpose.Verification);

                if (features != null)
                {
                    SafeMakeReport("✓ Features extracted");
                    CheckForMatchAndRecordAttendance(features);
                }
                else
                {
                    SafeMakeReport("✗ Failed to extract features");
                }
            }
            catch (Exception ex)
            {
                SafeMakeReport($"Error: {ex.Message}");
            }
        }

        private void CheckForMatchAndRecordAttendance(DPFP.FeatureSet features)
        {
            string connectionString = "server=localhost;user id=root;password=;database=biometric;SslMode=None;";
            string query = "SELECT uid, student_name, course, year_level, fingerprint_data FROM register_student";

            try
            {
                using (var connection = new MySqlConnection(connectionString))
                using (var command = new MySqlCommand(query, connection))
                {
                    connection.Open();

                    using (var reader = command.ExecuteReader())
                    {
                        while (reader.Read())
                        {
                            string uid = reader["uid"].ToString();
                            string name = reader["student_name"].ToString();
                            string course = reader["course"].ToString();
                            string year = reader["year_level"].ToString();

                            if (reader["fingerprint_data"] is byte[] templateData && templateData.Length > 0)
                            {
                                if (VerifyTemplate(features, templateData))
                                {
                                    // SHOW SUCCESS ON UI THREAD - RECORD ATTENDANCE
                                    if (this.InvokeRequired)
                                    {
                                        this.Invoke(new Action(() =>
                                        {
                                            OnMatchFoundForAttendance(name, uid, course, year);
                                        }));
                                    }
                                    else
                                    {
                                        OnMatchFoundForAttendance(name, uid, course, year);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                }

                // No match found - SHOW ON UI THREAD
                if (this.InvokeRequired)
                {
                    this.Invoke(new Action(() =>
                    {
                        SafeMakeReport("✗ No matching fingerprint found");
                        MessageBox.Show(this, "Verification failed. No match found.", "Access Denied",
                                      MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }));
                }
                else
                {
                    SafeMakeReport("✗ No matching fingerprint found");
                    MessageBox.Show(this, "Verification failed. No match found.", "Access Denied",
                                  MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            }
            catch (Exception ex)
            {
                SafeMakeReport($"Database error: {ex.Message}");
            }
        }

        private bool VerifyTemplate(DPFP.FeatureSet features, byte[] templateData)
        {
            try
            {
                using (var stream = new MemoryStream(templateData))
                {
                    var storedTemplate = new DPFP.Template();
                    storedTemplate.DeSerialize(stream);

                    var result = new DPFP.Verification.Verification.Result();
                    var verificator = new DPFP.Verification.Verification();

                    verificator.Verify(features, storedTemplate, ref result);

                    SafeMakeReport($"Verification score: {result.FARAchieved}");
                    return result.Verified;
                }
            }
            catch (Exception ex)
            {
                SafeMakeReport($"Verification error: {ex.Message}");
                return false;
            }
        }

        private void OnMatchFoundForAttendance(string name, string uid, string course, string year)
        {
            SafeMakeReport($"✓ FINGERPRINT VERIFIED!");
            SafeMakeReport($"Student: {name}");
            SafeMakeReport($"UID: {uid}");
            SafeMakeReport($"Recording attendance...");

            try
            {
                // Record attendance directly to database
                string connectionString = "server=localhost;user id=root;password=;database=biometric;SslMode=None;";
                string checkInTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                
                // Get student ID from register_student table
                int studentId = 0;
                using (var connection = new MySqlConnection(connectionString))
                {
                    connection.Open();
                    string studentQuery = "SELECT id FROM register_student WHERE uid = @uid";
                    using (var command = new MySqlCommand(studentQuery, connection))
                    {
                        command.Parameters.AddWithValue("@uid", uid);
                        var result = command.ExecuteScalar();
                        if (result != null)
                        {
                            studentId = Convert.ToInt32(result);
                        }
                    }
                }

                if (studentId == 0)
                {
                    ShowErrorDialog("Error", "Student not found in database");
                    return;
                }

                // Check if attendance already exists
                bool attendanceExists = false;
                using (var connection = new MySqlConnection(connectionString))
                {
                    connection.Open();
                    string checkQuery = "SELECT COUNT(*) FROM admin_attendance WHERE student_id = @studentId AND event_id = @eventId";
                    using (var command = new MySqlCommand(checkQuery, connection))
                    {
                        command.Parameters.AddWithValue("@studentId", studentId);
                        command.Parameters.AddWithValue("@eventId", selectedEventId);
                        attendanceExists = Convert.ToInt32(command.ExecuteScalar()) > 0;
                    }
                }

                if (attendanceExists)
                {
                    ShowErrorDialog("Attendance Already Recorded", "You have already recorded attendance for this event.");
                    return;
                }

                // Get event details for time calculation
                EventData selectedEvent = events.FirstOrDefault(e => e.event_id == selectedEventId);
                if (selectedEvent == null)
                {
                    ShowErrorDialog("Error", "Selected event not found");
                    return;
                }

                // Calculate if student is late
                DateTime eventStartTime = DateTime.Parse(selectedEvent.date + " " + selectedEvent.start_time);
                DateTime checkInDateTime = DateTime.Parse(checkInTime);
                int minutesLate = 0;
                string status = "present";

                if (checkInDateTime > eventStartTime)
                {
                    TimeSpan difference = checkInDateTime - eventStartTime;
                    minutesLate = (int)difference.TotalMinutes;
                    status = "late";
                }

                // Record attendance in admin_attendance table
                using (var connection = new MySqlConnection(connectionString))
                {
                    connection.Open();
                    string insertQuery = "INSERT INTO admin_attendance (student_id, event_id, check_in_time, status, minutes_late) " +
                                       "VALUES (@studentId, @eventId, @checkInTime, @status, @minutesLate)";
                    
                    using (var command = new MySqlCommand(insertQuery, connection))
                    {
                        command.Parameters.AddWithValue("@studentId", studentId);
                        command.Parameters.AddWithValue("@eventId", selectedEventId);
                        command.Parameters.AddWithValue("@checkInTime", checkInTime);
                        command.Parameters.AddWithValue("@status", status);
                        command.Parameters.AddWithValue("@minutesLate", minutesLate);
                        
                        command.ExecuteNonQuery();
                    }
                }

                // Update students_events table
                using (var connection = new MySqlConnection(connectionString))
                {
                    connection.Open();
                    string updateQuery = "UPDATE students_events SET attendance_status = @status, time_in = @timeIn, date_recorded = NOW() " +
                                       "WHERE student_id = @studentId AND event_id = @eventId";
                    
                    using (var command = new MySqlCommand(updateQuery, connection))
                    {
                        command.Parameters.AddWithValue("@status", status);
                        command.Parameters.AddWithValue("@timeIn", checkInTime);
                        command.Parameters.AddWithValue("@studentId", studentId);
                        command.Parameters.AddWithValue("@eventId", selectedEventId);
                        
                        command.ExecuteNonQuery();
                    }
                }

                // Show success dialog
                var attendanceData = new AttendanceData
                {
                    status = status,
                    check_in_time = checkInTime,
                    minutes_late = minutesLate
                };

                ShowAttendanceSuccessDialog(name, uid, course, year, attendanceData);
            }
            catch (Exception ex)
            {
                ShowErrorDialog("Error Recording Attendance", ex.Message);
            }
        }

        private void ShowAttendanceSuccessDialog(string name, string uid, string course, string year, AttendanceData attendance)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action(() => ShowAttendanceSuccessDialog(name, uid, course, year, attendance)));
                return;
            }

            // Create custom form for attendance success dialog
            Form successDialog = new Form()
            {
                Text = "Attendance Recorded Successfully",
                Size = new System.Drawing.Size(500, 400),
                StartPosition = FormStartPosition.CenterScreen,
                FormBorderStyle = FormBorderStyle.FixedDialog,
                MaximizeBox = false,
                MinimizeBox = false,
                BackColor = System.Drawing.Color.DarkGreen,
                ForeColor = System.Drawing.Color.White
            };

            // Add title label
            Label titleLabel = new Label()
            {
                Text = "ATTENDANCE RECORDED:",
                Font = new System.Drawing.Font("Arial", 16, System.Drawing.FontStyle.Bold),
                ForeColor = System.Drawing.Color.White,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 20)
            };

            // Add student details
            Label nameLabel = new Label()
            {
                Text = $"Student: {name}",
                Font = new System.Drawing.Font("Arial", 12),
                ForeColor = System.Drawing.Color.White,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 60)
            };

            Label uidLabel = new Label()
            {
                Text = $"UID: {uid}",
                Font = new System.Drawing.Font("Arial", 12),
                ForeColor = System.Drawing.Color.White,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 90)
            };

            Label courseLabel = new Label()
            {
                Text = $"Course: {course}",
                Font = new System.Drawing.Font("Arial", 12),
                ForeColor = System.Drawing.Color.White,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 120)
            };

            Label yearLabel = new Label()
            {
                Text = $"Year: {year}",
                Font = new System.Drawing.Font("Arial", 12),
                ForeColor = System.Drawing.Color.White,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 150)
            };

            // Add attendance details
            Label statusLabel = new Label()
            {
                Text = $"Status: {attendance.status.ToUpper()}",
                Font = new System.Drawing.Font("Arial", 12, System.Drawing.FontStyle.Bold),
                ForeColor = attendance.status == "late" ? System.Drawing.Color.Yellow : System.Drawing.Color.LightGreen,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 190)
            };

            Label timeLabel = new Label()
            {
                Text = $"Check-in Time: {attendance.check_in_time}",
                Font = new System.Drawing.Font("Arial", 12),
                ForeColor = System.Drawing.Color.White,
                AutoSize = true,
                Location = new System.Drawing.Point(20, 220)
            };

            if (attendance.minutes_late > 0)
            {
                Label lateLabel = new Label()
                {
                    Text = $"Minutes Late: {attendance.minutes_late}",
                    Font = new System.Drawing.Font("Arial", 12),
                    ForeColor = System.Drawing.Color.Yellow,
                    AutoSize = true,
                    Location = new System.Drawing.Point(20, 250)
                };
                successDialog.Controls.Add(lateLabel);
            }

            // Add OK button
            Button okButton = new Button()
            {
                Text = "OK",
                Size = new System.Drawing.Size(100, 35),
                Location = new System.Drawing.Point(200, 320),
                BackColor = System.Drawing.Color.White,
                ForeColor = System.Drawing.Color.LimeGreen,
                Font = new System.Drawing.Font("Arial", 10, System.Drawing.FontStyle.Bold),
                DialogResult = DialogResult.OK
            };

            okButton.Click += (sender, e) =>
            {
                successDialog.DialogResult = DialogResult.OK;
                successDialog.Close();
            };

            // Add controls to form
            successDialog.Controls.Add(titleLabel);
            successDialog.Controls.Add(nameLabel);
            successDialog.Controls.Add(uidLabel);
            successDialog.Controls.Add(courseLabel);
            successDialog.Controls.Add(yearLabel);
            successDialog.Controls.Add(statusLabel);
            successDialog.Controls.Add(timeLabel);
            successDialog.Controls.Add(okButton);

            // Accept button for Enter key
            successDialog.AcceptButton = okButton;

            // Show dialog and close form when OK is clicked
            if (successDialog.ShowDialog() == DialogResult.OK)
            {
                this.DialogResult = DialogResult.OK;
                this.Close();
            }
        }

        private void ShowErrorDialog(string title, string message)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action(() => ShowErrorDialog(title, message)));
                return;
            }

            MessageBox.Show(this, message, title, MessageBoxButtons.OK, MessageBoxIcon.Error);
        }

        private void SafeMakeReport(string message)
        {
            if (this.InvokeRequired)
            {
                this.Invoke(new Action(() => MakeReport(message)));
            }
            else
            {
                MakeReport(message);
            }
        }
    }
}
