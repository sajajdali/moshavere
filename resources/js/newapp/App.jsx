import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { SiteProvider } from './lib/SiteContext.jsx';
import Layout from './components/Layout.jsx';
import ScrollToTop from './components/ScrollToTop.jsx';
import LoginModal from './components/LoginModal.jsx';

import Home from './pages/Home.jsx';
import Search from './pages/Search.jsx';
import AboutUs from './pages/AboutUs.jsx';
import ContactUs from './pages/ContactUs.jsx';
import DoctorProfile from './pages/DoctorProfile.jsx';
import AppointmentDetails from './pages/AppointmentDetails.jsx';
import PatientProfile from './pages/PatientProfile.jsx';
import OnlineVisitChat from './pages/OnlineVisitChat.jsx';
import Login from './pages/Login.jsx';
import AdminLogin from './pages/AdminLogin.jsx';
import Feedback from './pages/Feedback.jsx';
import ServicePage from './pages/ServicePage.jsx';
import NotFound from './pages/NotFound.jsx';

export default function App({ bootstrap }) {
  return (
    <SiteProvider bootstrap={bootstrap}>
      <BrowserRouter basename="/">
        <ScrollToTop />
        <LoginModal />
        <Routes>
          {/* صفحه چت تمام صفحه است و layout عمومی ندارد */}
          <Route path="/chat/:id" element={<OnlineVisitChat />} />
          <Route path="/admin" element={<AdminLogin />} />
          <Route path="/login-doctor" element={<AdminLogin variant="doctor" />} />

          <Route path="*" element={
            <Layout>
              <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/search" element={<Search />} />
                <Route path="/feedback/:code" element={<Feedback />} />
                <Route path="/service/:id" element={<ServicePage />} />
                <Route path="/service/:id/:name" element={<ServicePage />} />
                <Route path="/aboutus" element={<AboutUs />} />
                <Route path="/contact-us" element={<ContactUs />} />
                {/* بخش دوم (نام پزشک) اختیاری است تا لینک های قدیمی هم کار کنند */}
                <Route path="/doctor/:id" element={<DoctorProfile />} />
                <Route path="/doctor/:id/:name" element={<DoctorProfile />} />
                <Route path="/appointment/:code" element={<AppointmentDetails />} />
                <Route path="/profile" element={<PatientProfile />} />
                <Route path="/login" element={<Login />} />
                <Route path="/404" element={<NotFound />} />
                <Route path="*" element={<NotFound />} />
              </Routes>
            </Layout>
          } />
        </Routes>
      </BrowserRouter>
    </SiteProvider>
  );
}
