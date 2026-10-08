import React, { useState } from 'react';
import api from '../lib/api';
import useAsync from '../lib/useAsync';
import { useSite } from '../lib/SiteContext.jsx';
import { ErrorBox } from '../components/States.jsx';
import BookingSheet from '../components/BookingSheet.jsx';
import SingleDoctorHome from './home/SingleDoctorHome.jsx';
import HomeSkeleton from './home/HomeSkeleton.jsx';
import ClinicHome from './home/ClinicHome.jsx';
import SmallClinicHome from './home/SmallClinicHome.jsx';

const VARIANTS = {
  single_doctor: SingleDoctorHome,
  clinic: ClinicHome,
  single_doctor_with_doctors: SmallClinicHome,
};

export default function Home() {
  const { mode } = useSite();
  const [province, setProvince] = useState('');
  const { data, error, loading, reload } = useAsync(
    () => api.home(province ? { province } : undefined),
    [province],
  );
  const [booking, setBooking] = useState(null);

  // به جای قفل کردن کل صفحه، شکل کلی همان قالب با بلر و پالس نمایش داده می شود
  if (loading) return <HomeSkeleton mode={mode} />;
  if (error) return <ErrorBox error={error} onRetry={reload} />;

  const Variant = VARIANTS[mode] ?? SingleDoctorHome;

  return (
    <>
      <Variant data={data ?? {}} onBook={setBooking} province={province} onProvinceChange={setProvince} />
      {booking && (
        <BookingSheet
          doctorId={booking.doctorId}
          serviceId={booking.serviceId}
          onClose={() => setBooking(null)}
        />
      )}
    </>
  );
}
